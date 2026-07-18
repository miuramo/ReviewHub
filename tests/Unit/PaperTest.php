<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class PaperTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_create_a_paper(): void
    {
        $user10 = User::factory()->create();
        $user11 = User::factory()->create();
        $user12 = User::factory()->create();
        $paper1 = Paper::create([
            'category_id' => 1,
            'contactemails' => $user11->email."\n",$user12->email,
            'owner' => $user10->id,
        ]);
        $paper1->updateContacts();

        $user20 = User::factory()->create();
        $user21 = User::factory()->create();
        $user22 = User::factory()->create();
        $paper2 = Paper::create([
            'category_id' => 2,
            'contactemails' => $user21->email."\n",$user22->email,
            'owner' => $user20->id,
        ]);
        $paper2->updateContacts();

        $this->assertTrue($paper1->owner == $user10->id);

        $this->assertTrue($paper1->id_03d() == sprintf(env('PID_FORMAT','%04d'),$paper1->id));
        $this->assertTrue(Contact::where('email', $user20->email)->get() != null);
        $this->assertTrue(Contact::where('email', $user21->email)->get() != null);
    }

    /**
     * resubmit_until の日付計算が意図通り動作することを確認する。
     * PaperController::confirmreview() の
     *   date('Y-m-d', strtotime($sub->ec_decision_at . ' + ' . $resubmit_duration_days . ' days'))
     * と同じ式をテストする。
     */
    public function test_resubmit_until_date_calculation(): void
    {
        // 基本ケース: 30日後
        $this->assertSame(
            '2025-01-31',
            date('Y-m-d', strtotime('2025-01-01' . ' + ' . 30 . ' days'))
        );

        // 月末をまたぐケース: 2025-01-15 + 30日 = 2025-02-14
        $this->assertSame(
            '2025-02-14',
            date('Y-m-d', strtotime('2025-01-15' . ' + ' . 30 . ' days'))
        );

        // 年末をまたぐケース: 2025-12-15 + 30日 = 2026-01-14
        $this->assertSame(
            '2026-01-14',
            date('Y-m-d', strtotime('2025-12-15' . ' + ' . 30 . ' days'))
        );

        // うるう年: 2024-02-01 + 28日 = 2024-02-29
        $this->assertSame(
            '2024-02-29',
            date('Y-m-d', strtotime('2024-02-01' . ' + ' . 28 . ' days'))
        );

        // 非うるう年: 2025-02-01 + 28日 = 2025-03-01
        $this->assertSame(
            '2025-03-01',
            date('Y-m-d', strtotime('2025-02-01' . ' + ' . 28 . ' days'))
        );

        // ec_decision_at が datetime 型（時刻付き）の場合も日付部分だけが加算されること
        $this->assertSame(
            '2025-03-31',
            date('Y-m-d', strtotime('2025-03-01 14:30:00' . ' + ' . 30 . ' days'))
        );

        // デフォルト値 30日のケース（Setting::getary の fallback）
        $resubmit_duration_days = 30;
        $this->assertSame(
            '2025-06-30',
            date('Y-m-d', strtotime('2025-05-31' . ' + ' . $resubmit_duration_days . ' days'))
        );
    }
}
