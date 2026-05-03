<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MetaModel extends Model
{
    use HasFactory;

    public static function column_details(string $tableName): array
    {
        $driver = DB::connection()->getDriverName();
        $coldetails = [];
        if ($driver === 'sqlite') {
            $columns = DB::select("pragma table_info('{$tableName}')");
            foreach ($columns as $cc) {
                $coldetails[$cc->name] = $cc->type;
            }
        } else if ($driver === 'mysql') {
            $columns = DB::select("show full columns from `{$tableName}`");
            // カラム名とデータ型の取得
            foreach ($columns as $colary) {
                $coldetails[$colary->Field] = $colary->Type;
            }
        }
        // Type() のかっこ以下は取り除く
        foreach ($coldetails as $f => $t) {
            $pos = strpos($t, '(');
            if ($pos > 0) {
                $coldetails[$f] = substr($t, 0, $pos);
            }
        }
        return $coldetails;
    }

    public static function get_db_tables(): array
    {
        $driver = DB::connection()->getDriverName();
        $tables = [];
        if ($driver === 'sqlite' || $driver === 'sqlite_testing') {
            $_tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $tables = array_map(function ($item) {
                return $item->name;
            }, $_tables);
        } else if ($driver === 'mysql') {
            $_tables = DB::select('SHOW TABLES');
            foreach ($_tables as $nnn => $obj) {
                foreach ($obj as $nnnn => $tn) {
                    $tables[] = $tn;
                }
            } // ここは1回しかまわらないはず
        }
        sort($tables);
        return $tables;
    }
    public function get_table_comments(): array
    {
        $domain = config('database.default');
        $db_name = config('database.connections.' . str_replace('.', '_', $domain) . '.database');
        $tableName = $this->getTable();
        return self::get_table_comments_from_db($db_name, $tableName);
    }

    public static function get_table_comments_from_db(string $dbName, string $tableName): array
    {
        $driver = DB::connection()->getDriverName();
        $coldetails = [];
        if ($driver === 'sqlite') {
            $columns = DB::select("pragma table_info('{$tableName}')");
            foreach ($columns as $cc) {
                $coldetails[$cc->name] = $cc->name;
            }
        } else if ($driver === 'mysql') {
            $columns = DB::select("SELECT COLUMN_NAME, COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '{$dbName}' AND TABLE_NAME = '{$tableName}'");
            // カラム名とデータ型の取得
            foreach ($columns as $colary) {
                $coldetails[$colary->COLUMN_NAME] = $colary->COLUMN_COMMENT;
            }
        }
        return $coldetails;
    }

    public static function ary2serial(array $ary): string
    {
        $s = serialize($ary);
        $k = md5($s . env('APP_KEY'));
        $master = $s . substr($k, 0, 8);
        $gz = gzdeflate($master);
        $val = base64_encode($gz);
        return str_replace(['+', '/', '='], ['_', '-', '.'], $val);
    }
    public static function serial2ary(string $serial): array|false
    {
        $val = str_replace(['_', '-', '.'], ['+', '/', '='], $serial);
        $gz = base64_decode($val);
        $master = gzinflate($gz);
        $k = substr($master, -8);
        $s = substr($master, 0, -8);
        $m = md5($s . env('APP_KEY'));
        if (substr($m, 0, 8) != $k) {
            return false;
        }
        return unserialize($s);
    }
}
