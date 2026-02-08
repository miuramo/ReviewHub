<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // サポートするロケールを取得（config もしくはハードコード）
        $supported = Config::get('app.supported_locales', ['ja', 'en']);

        // Request::getPreferredLanguage は Symfony の実装を使う
        // 引数に配列を渡すと最も優先するサポート言語を返す（例: 'en', 'ja'）
        Log::info('Accept-Language Header: ' . $request->header('Accept-Language'));
        $preferred = $request->getPreferredLanguage($supported);
        Log::info("Preferred locale from Symfony: {$preferred}");

        // getPreferredLanguage が null を返す可能性があるため安全にフォールバック
        $locale = $preferred ?? Config::get('app.locale');
        Log::info("Final locale to be set: {$locale}");

        // 安全: 一応サポート配列にあるか確認（getPreferredLanguage で保証されるが二重チェック）
        if (! in_array($locale, $supported)) {
            $locale = Config::get('app.locale');
        }

        // アプリケーションロケールを設定
        App::setLocale($locale);

        // Carbon のロケールも合わせる（日時表示など）
        try {
            Carbon::setLocale($locale);
        } catch (\Throwable $e) {
            // Carbon がロケールを受け付けない場合があるので例外は無視
        }

        // ロケール依存のローカライズ関数が必要なら setlocale も設定（任意）
        // setlocale(LC_TIME, $locale === 'ja' ? 'ja_JP.UTF-8' : 'en_US.UTF-8');

        return $next($request);
    }
}
