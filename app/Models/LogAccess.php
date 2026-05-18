<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
            Log::channel('single')->warning('Failed to decode request JSON in LogAccess', [
                'error' => $e->getMessage(),
                'value' => substr($cleanValue, 0, 500)
            ]);
            $ary = [
                'error' => 'Failed to decode request JSON LogAccess:48',
                'exception_message' => $e->getMessage(),
                'value_for_debug' => substr($cleanValue, 0, 500)
            ];
            if (auth()->check()) {
                $ary = [
                    'uid_for_debug' => auth()->user()->uid,
                    'name_for_debug' => auth()->user()->name,
                    'email_for_debug' => auth()->user()->email
                ];
            }
            // add url and method to log context
            $ary['url'] = $this->url;
            $ary['method'] = $this->method;
            return $ary;
        }
    }

    /**
     * Mutator for request attribute to ensure clean UTF-8 encoding
     */
    public function setRequestAttribute(mixed $value): void
    {
        if (is_null($value)) {
            $this->attributes['request'] = null;
            return;
        }

        // Always normalize to a JSON string for stable persistence.
        if (is_string($value)) {
            $cleanValue = $this->cleanUtf8($value);
        } else {
            $cleanValue = $this->cleanUtf8Recursive($value);
        }

        try {
            $this->attributes['request'] = json_encode(
                $cleanValue,
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            Log::channel('single')->warning('Failed to encode request JSON in LogAccess', [
                'error' => $e->getMessage(),
                'url' => $this->url ?? null,
                'method' => $this->method ?? null,
            ]);

            // Last-resort fallback so log insertion itself never fails.
            $this->attributes['request'] = '{}';
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
            $cleanArray = [];
            foreach ($data as $key => $value) {
                $cleanKey = is_string($key) ? $this->cleanUtf8($key) : $key;
                $cleanArray[$cleanKey] = $this->cleanUtf8Recursive($value);
            }
            return $cleanArray;
        } elseif (is_object($data)) {
            if ($data instanceof \JsonSerializable) {
                return $this->cleanUtf8Recursive($data->jsonSerialize());
            }

            if ($data instanceof \Stringable) {
                return $this->cleanUtf8((string) $data);
            }

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
