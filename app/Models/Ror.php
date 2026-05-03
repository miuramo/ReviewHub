<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ror extends Model
{
    protected $fillable = [
        'affil',
        'ror',
    ];
    //

    public static function affil2ror(): array
    {
        $rors = self::where('valid', true)->pluck('ror', 'affil')->toArray();
        return $rors;
    }

    public static function getRor(string $affil): ?string
    {
        $from_db = self::where('affil', $affil)->where('valid', true)->first();
        if (!$from_db) {
            $rortxt = self::fetchRor($affil);
            if ($rortxt) {
                $obj = self::create([
                    'affil' => $affil,
                    'ror' => $rortxt,
                ]);
                $obj->save();
                return $rortxt;
            }
            return null;
        } else {
            return $from_db->ror;
        }
    }

    /**
     * ROR取得
     * https://ror.org/ から所属機関のRORを取得する
     */
    private static function fetchRor(string $affil = ""): ?string
    {
        $affil = trim($affil);
        if (strlen($affil) == 0) return null;
        $url = "https://api.ror.org/organizations?query=" . urlencode($affil);
        try {
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            if (isset($data['items']) && count($data['items']) > 0) {
                $first_item = $data['items'][0];
                if (isset($first_item['id'])) {
                    return $first_item['id'];
                }
            }
        } catch (\Exception $e) {
            // エラーハンドリング
        }
        return null;
    }
}
