<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\LogAccess as ModelsLogAccess; // ミドルウェアのLogAccessとかぶるので、別名
use Illuminate\Support\Facades\Log;

class LogAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hozon = $next($request);

        $rooturl = $request->root();
        $uid = (Auth::id() != null) ? Auth::id() : -1;

        // パスワードが生で保存されるのを避ける
        $allreq = $request->all();
        // unset($allreq['password']);
        $hidden = ['password', 'current_password', 'password_confirmation'];
        foreach ($hidden as $h) {
            if (isset($allreq[$h])) $allreq[$h] = '(hidden)';
        }

        // より堅牢なUTF-8クリーニング処理
        array_walk_recursive($allreq, function (&$value) {
            if (is_string($value)) {
                // 不正なUTF-8文字を除去/置換
                $encoded = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                if ($encoded === false) {
                    $value = '(invalid UTF-8)';
                } else {
                    $value = $encoded;
                }
                // null バイトを除去
                $value = str_replace("\0", '', $value ?? '');

                // 制御文字を除去（改行とタブは保持）
                $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
            }
        });

        $url = substr($request->fullUrl(), strlen($rooturl));
        if ($url == '/file_favicon' || $url == '/livewire/update' || strlen($url) == 0) return $hozon; // faviconのアクセスはログに残さない

        // paper_id の推測
        // URLから論文IDを推測する
        // /paper/{paper_id}
        // /admin_paper/{paper_id}
        // /logac/paper/{paper_id}
        // もし、上記のいずれかのパターンにマッチしたら、paper_idを抽出して保存する
        if (
            preg_match('/\/paper\/(\d+)/', $url, $matches) ||
            preg_match('/\/admin_paper\/(\d+)/', $url, $matches) ||
            preg_match('/\/logac\/paper\/(\d+)/', $url, $matches)
        ) {
            $estimated_paper_id = (int)$matches[1];
        } else if (preg_match('/\/bb\/(\d+)/', $url, $matches)) {
            // /bb/{bb_id} -> bb_idからpaper_idを推測
            $bb_id = (int)$matches[1];
            // bb_idからpaper_idを推測するロジック（例: BBテーブルを参照）
            $estimated_paper_id = \App\Models\Bb::where('id', $bb_id)->value('paper_id');
        } else if (
            preg_match('/\/review\/(\d+)/', $url, $matches) ||
            preg_match('/\/review_request\/confirm\/(\d+)/', $url, $matches) ||
            preg_match('/\/review_request\/confirmpost\/(\d+)/', $url, $matches) ||
            preg_match('/\/logac\/review\/(\d+)/', $url, $matches)
        ) {
            // /review_request/confirm/{review_id}
            // /review_request/confirmpost/{review_id}
            // /review/{review_id} -> review_idからpaper_idを推測
            // /logac/review/{review_id}
            $review_id = (int)$matches[1];
            // review_idからpaper_idを推測するロジック（例: Reviewテーブルを参照）
            $estimated_paper_id = \App\Models\Review::where('id', $review_id)->value('paper_id');
            // Log::channel('single')->info("LogAccess Middleware: Detected review_id={$review_id} in URL, attempting to estimate paper_id {$estimated_paper_id}");
        } else if (preg_match('/\/file\/(\d+)\/show/', $url, $matches)) {
            // /file/{file}/show -> file_idからpaper_idを推測
            $file_id = (int)$matches[1];
            // file_idからpaper_idを推測するロジック（例: Fileテーブルを参照）
            $estimated_paper_id = \App\Models\File::where('id', $file_id)->value('paper_id');
        } else if (
            preg_match('/\/sub\/(\d+)/', $url, $matches) ||
            preg_match('/\/paper_reviewresult\/(\d+)/', $url, $matches) ||
            preg_match('/\/paper_confirmreviewresult\/(\d+)/', $url, $matches) ||
            preg_match('/\/admin_submit_proceed\/(\d+)/', $url, $matches) ||
            preg_match('/\/admin_submit_sendreceipt\/(\d+)/', $url, $matches) ||
            preg_match('/\/admin_submit_sendreceipt_final\/(\d+)/', $url, $matches) ||
            preg_match('/\/admin_submit_senddisclose\/(\d+)/', $url, $matches) || 
            preg_match('/\/reviewcomment_sub\/(\d+)/', $url, $matches)
        ) {
            // /paper_reviewresult/{sub_id}
            // /paper_confirmreviewresult/{sub_id}
            // /sub/{sub_id} -> sub_idからpaper_idを推測
            // /admin_submit_proceed/{sub_id}
            // /admin_submit_sendreceipt/{sub_id}
            // /admin_submit_sendreceipt_final/{sub_id}
            // /admin_submit_senddisclose/{sub_id}
            // /reviewcomment_sub/{sub_id}
            $sub_id = (int)$matches[1];
            // sub_idからpaper_idを推測するロジック（例: Subテーブルを参照）
            $estimated_paper_id = \App\Models\Submit::where('id', $sub_id)->value('paper_id');
        } else {
            $estimated_paper_id = null;
        }

        try {
            $accessLog = new ModelsLogAccess([
                'uid' => $uid,
                'url' => $url,
                'paper_id' => $estimated_paper_id,
                'method' => $request->method(),
                'request' => $allreq, //'-',// $request->headers->all(),
            ]);
            $accessLog->save();
        } catch (\Exception $e) {
            Log::channel('single')->error("LogAccess Middleware Error: " . $e->getMessage());
            Log::channel('single')->info(json_encode($allreq));
        }

        return $hozon;
    }
}
