<?php

namespace Tests\Feature\Auth;

use App\Models\MagicLoginLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MagicLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('role_any', fn (User $user, string $roles): bool =>
            $user->email === 'magic-admin@example.test' && str_contains($roles, 'admin')
        );
    }

    public function test_admin_can_issue_and_use_a_magic_link_once(): void
    {
        Notification::fake();

        /** @var User $admin */
        $admin = User::factory()->create(['email' => 'magic-admin@example.test']);
        $target = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.magic-links.store'), [
            'email' => $target->email,
        ]);

        $response->assertRedirect(route('admin.magic-links'))
            ->assertSessionHas('magic_link_url');

        $url = $response->getSession()->get('magic_link_url');
        $passwordResetUrl = $response->getSession()->get('password_reset_url');
        $token = basename(parse_url($url, PHP_URL_PATH));
        $link = MagicLoginLink::firstOrFail();

        $this->get(route('admin.magic-links'))
            ->assertOk()
            ->assertSee($url)
            ->assertSee($passwordResetUrl);

        $this->assertNotEmpty($passwordResetUrl);
        Notification::assertNothingSent();
        $this->post(route('logout'));
        $this->get($passwordResetUrl)->assertOk();

        $this->assertSame(hash('sha256', $token), $link->token_hash);
        $this->assertSame($target->id, $link->user_id);

        $this->get(route('magic-login.show', ['token' => $token]))
            ->assertOk();
        $this->assertNull($link->fresh()->used_at);

        $this->post(route('magic-login.consume', ['token' => $token]))
            ->assertRedirect();
        $this->assertAuthenticatedAs($target);
        $this->assertNotNull($link->fresh()->used_at);

        $this->post(route('magic-login.consume', ['token' => $token]))
            ->assertNotFound();
    }

    public function test_non_admin_cannot_issue_a_magic_link(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.magic-links.store'), ['email' => $target->email])
            ->assertForbidden();

        $this->assertDatabaseCount('magic_login_links', 0);
    }

    public function test_expired_magic_link_cannot_be_used(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['email' => 'magic-admin@example.test']);
        $target = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.magic-links.store'), [
            'email' => $target->email,
        ]);
        $token = basename(parse_url($response->getSession()->get('magic_link_url'), PHP_URL_PATH));

        $this->travel(25)->hours();
        $this->post(route('magic-login.consume', ['token' => $token]))
            ->assertNotFound();

        $this->assertAuthenticatedAs($admin);
    }
}