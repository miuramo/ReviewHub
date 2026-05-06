<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class LogTestController extends Controller
{
    /**
     * テスト用：わざとエラーを発生させる
     */
    public function testError()
    {
        try {
            // 15行目 - この行で意図的にエラーを発生させる
            $this->causeError();
            
            return response()->json(['message' => 'エラーが発生しませんでした']);
        } catch (\Exception $e) {
            // ログに記録（Slackに送信される）
            Log::error('テストエラーが発生しました', [
                'exception' => $e,
                'file' => __FILE__,
                'line' => __LINE__,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'エラーをログに記録しました',
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'current_file' => __FILE__,
                'current_line' => __LINE__
            ]);
        }
    }
    
    /**
     * 34行目 - エラーを発生させるメソッド
     */
    private function causeError()
    {
        User::findOrFail(-1); // 存在しないユーザーを検索してエラーを発生させる
        // 37行目 - 未定義変数にアクセスしてエラーを発生
        $undefinedArray = null;
        return $undefinedArray['nonexistent_key']; // この行でエラーが発生する
    }

    /**
     * 451エラーのテスト（フィルタされるべきエラー）
     */
    public function test451Error()
    {
        try {
            throw new \Exception('HTTP 451 Unavailable For Legal Reasons - 4.4.2');
        } catch (\Exception $e) {
            Log::error('451エラーのテスト（これはSlackに送信されないはず）', [
                'exception' => $e,
                'file' => __FILE__,
                'line' => __LINE__
            ]);
            
            return response()->json([
                'message' => '451エラーをログに記録しました（フィルタされるはず）'
            ]);
        }
    }

    /**
     * 通常のエラーのテスト（送信されるべきエラー）
     */
    public function testNormalError()
    {
        try {
            // 63行目 - この行で通常のエラーを発生させる
            throw new \Exception('通常のエラーメッセージ - これはSlackに送信されるはず');
        } catch (\Exception $e) {
            Log::error('通常エラーのテスト', [
                'exception' => $e,
                'file' => __FILE__,
                'line' => __LINE__,
                'test_info' => 'このエラーはSlackに送信されるはず'
            ]);
            
            return response()->json([
                'message' => '通常エラーをログに記録しました',
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'check_logs' => 'storage/logs/laravel.logとerror_logを確認してください'
            ]);
        }
    }

    /**
     * デバッグ用：単純なメッセージのテスト
     */
    public function testSimpleMessage()
    {
        Log::error('これは単純なテストメッセージです');
        
        return response()->json([
            'message' => 'シンプルなログメッセージを記録しました',
            'check' => 'Slackとファイルログを確認してください'
        ]);
    }
}