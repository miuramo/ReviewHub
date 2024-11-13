<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfirmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kakunin = [
            "chk1" => "下のボタンを押して新規投稿情報を作成したあと、必要なファイルや情報をアップロードしていただくと、投稿申込完了となります。投稿状況の確認や投稿の修正は「投稿一覧」から行ってください。",
        ];
        foreach($kakunin as $nm=>$mes){
            \App\Models\Confirm::factory()->create([
                'name' => $nm,
                'mes' => $mes,
                'grp' => 1,
            ]);
        }
        $mailkakunin = [
            "chk5" => "投稿者への連絡はメールで行います。メールが受信できない状況によって生じる不利益は、すべて投稿者が負うことを了解しました。",
            "chk6" => "本システムは送信エラー（転送の失敗を含む）が複数回発生したアドレスを自動的に削除します。また削除した旨を投稿連絡用メールアドレスに通知します。その際には共著者と連携して問題解決を図っていただきます。",
            "chk7" => "投稿連絡用メールアドレスは投稿締切後も追加・修正できます。ただし新規に追加されたアドレスへ、過去の通知を再送信することはありません。また投稿に紐づけられた共著者は投稿情報の参照はできますが、ファイルの差し替えや入力情報の修正はできません。これらのシステムの動作について、了解しました。",
            "chk8" => "投稿連絡用メールアドレスの入力を誤ると、意図しない人が通知を受け取ったり、投稿情報を閲覧したりする恐れがあります。このことによって生じる不利益は、投稿者が負うことを了解しました。",
        ];
        foreach($mailkakunin as $nm=>$mes){
            \App\Models\Confirm::factory()->create([
                'name' => $nm,
                'mes' => $mes,
                'grp' => 2,
            ]);
        }

        //
    }
}
