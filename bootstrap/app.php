<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__ . '/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\App\Http\Middleware\BlockSuspiciousRequests::class);
        $middleware->append(\App\Http\Middleware\SetLocaleMiddleware::class);
        $middleware->append(\App\Http\Middleware\LogAccess::class);
        $middleware->append(\App\Http\Middleware\ReplaceKutenMiddleware::class);
        $middleware->append(\App\Http\Middleware\TrustProxies::class);

        // $middleware->append(\App\Http\Middleware\EncryptCookies::class);
        // $middleware->append(\Illuminate\Session\Middleware\StartSession::class);
        // $middleware->append(\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class);
        // $middleware->append(\Illuminate\View\Middleware\ShareErrorsFromSession::class);
        // $middleware->append(\App\Http\Middleware\VerifyCsrfToken::class);
        // $middleware->append(\App\Http\Middleware\CheckDomain::class);
        // $middleware->append(\Illuminate\Routing\Middleware\SubstituteBindings::class);
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('app:check-failed-jobs')->everyTenMinutes();

        // リマインダメール送信（本番: 2日に1回 朝7:30、それ以外: テスト用に1分おき）
        if (env('APP_DEBUG', false) == false || app()->environment('production')) {
            $schedule->call([\App\Models\Task::class, 'sendReminderEmails'])->cron('30 7 */2 * *');
        } else {
            // $schedule->call([\App\Models\Task::class, 'sendReminderEmails'])->everyMinute();
        }
    });

$application = $app->create();

$envFile = getenv('X_APP_ENV_FILE') ?: '.env';
$application->loadEnvironmentFrom(basename($envFile));

return $application;
