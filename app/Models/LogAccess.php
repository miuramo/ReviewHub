<?php

namespace App\Models;

use Illuminate\Container\Attributes\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LogAccess extends Model
{
    use HasFactory;

    protected $casts = [
        'request' => 'json',
    ];
    protected $fillable = [
        'uid',
        'url',
        'paper_id',
        'method',
        'request'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'uid');
    }

    /**
     * Accessor for request attribute to handle malformed UTF-8 characters
     */
    public function getRequestAttribute(mixed $value): ?array
    {
        if (is_null($value)) {
            return null;
        }
        
        // If already decoded, return as is
        if (is_array($value)) {
            return $value;
        }
        
        // Clean malformed UTF-8 characters before JSON decoding
        $cleanValue = $this->cleanUtf8($value);
        
        try {
            return json_decode($cleanValue, true);
        } catch (\Exception $e) {
            \Log::warning('Failed to decode request JSON in LogAccess', [
                'error' => $e->getMessage(),
                'value' => substr($cleanValue, 0, 500)
            ]);
            return [];
        }
    }

    /**
     * Mutator for request attribute to ensure clean UTF-8 encoding
     */
    public function setRequestAttribute(mixed $value): void
    {
        if (is_array($value) || is_object($value)) {
            // Apply UTF-8 cleaning recursively to array/object values
            $cleanValue = $this->cleanUtf8Recursive($value);
            $this->attributes['request'] = json_encode($cleanValue, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
        } else {
            $this->attributes['request'] = $value;
        }
    }

    /**
     * Clean malformed UTF-8 characters from a string
     */
    private function cleanUtf8(mixed $string): mixed
    {
        if (!is_string($string)) {
            return $string;
        }
        
        // Remove or replace invalid UTF-8 characters
        $clean = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        
        // null バイトを除去
        $clean = str_replace("\0", '', $clean ?? '');
        
        // 制御文字を除去（改行とタブは保持）
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $clean);
        
        return $clean;
    }

    /**
     * Recursively clean UTF-8 characters in arrays and objects
     */
    private function cleanUtf8Recursive(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map([$this, 'cleanUtf8Recursive'], $data);
        } elseif (is_object($data)) {
            $cleanObject = new \stdClass();
            foreach ($data as $key => $value) {
                $cleanKey = $this->cleanUtf8($key);
                $cleanObject->$cleanKey = $this->cleanUtf8Recursive($value);
            }
            return $cleanObject;
        } elseif (is_string($data)) {
            return $this->cleanUtf8($data);
        }
        
        return $data;
    }

    /**
     * App\Http\Middleware\LogAccess で、実際のアクセスログ保存処理を行っている。
     */

    public static function dates_sendrequest(string $review, string $revuid): Collection
    {
        $ret = LogAccess::where('url', "/task_sendrequest/{$review}/{$revuid}")
            ->where('method', 'GET')
            ->orderBy('created_at', 'asc')
            ->get();
        return $ret;
    }
}
