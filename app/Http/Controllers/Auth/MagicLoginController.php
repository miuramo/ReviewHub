<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MagicLoginLink;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MagicLoginController extends Controller
{
    private const LINK_TTL_HOURS = 24;
    private const PASSWORD_RESET_URL_GENERATED = 'password_reset_url_generated';

    public function index(): View
    {
        $this->authorizeManagement();

        return view('admin.magic-links');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email')->whereNull('deleted_at'),
            ],
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();
        $token = Str::random(64);
        $expiresAt = now()->addHours(self::LINK_TTL_HOURS);

        DB::transaction(function () use ($user, $request, $token, $expiresAt): void {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            MagicLoginLink::where('user_id', $user->id)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            MagicLoginLink::create([
                'user_id' => $user->id,
                'created_by' => $request->user()->id,
                'token_hash' => hash('sha256', $token),
                'expires_at' => $expiresAt,
            ]);
        });

        $passwordResetUrl = null;
        $passwordResetStatus = Password::sendResetLink(
            ['email' => $user->email],
            function (User $resetUser, string $resetToken) use (&$passwordResetUrl): string {
                $passwordResetUrl = url(route('password.reset', [
                    'token' => $resetToken,
                    'email' => $resetUser->getEmailForPasswordReset(),
                ], false));

                return self::PASSWORD_RESET_URL_GENERATED;
            }
        );

        $flash = [
            'magic_link_url' => route('magic-login.show', ['token' => $token]),
            'magic_link_email' => $user->email,
            'magic_link_expires_at' => $expiresAt->toDateTimeString(),
            'password_reset_status' => $passwordResetStatus,
        ];

        if ($passwordResetStatus === self::PASSWORD_RESET_URL_GENERATED) {
            $flash['password_reset_url'] = $passwordResetUrl;
        }

        return redirect()->route('admin.magic-links')->with($flash);
    }

    public function show(string $token): View
    {
        abort_unless($this->findValidLink($token) !== null, 404);

        return view('auth.magic-login', ['token' => $token]);
    }

    public function consume(Request $request, string $token): RedirectResponse
    {
        $user = DB::transaction(function () use ($token): ?User {
            $link = MagicLoginLink::where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if ($link === null || $link->used_at !== null || $link->expires_at->isPast()) {
                return null;
            }

            $user = User::find($link->user_id);
            if ($user === null) {
                return null;
            }

            $link->update(['used_at' => now()]);

            return $user;
        });

        abort_if($user === null, 404);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::home());
    }

    private function findValidLink(string $token): ?MagicLoginLink
    {
        return MagicLoginLink::where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    private function authorizeManagement(): void
    {
        abort_unless(auth()->user()?->can('role_any', 'admin|manager'), 403);
    }
}