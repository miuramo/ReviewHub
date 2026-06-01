<?php

namespace App\Livewire;

use App\Models\Paper;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class ContactEmailEditor extends Component
{
    public Paper $paper;

    public string $contactemails = '';

    /** @var string[] */
    public array $validationErrors = [];

    public bool $saved = false;

    public function mount(Paper $paper): void
    {
        $this->paper = $paper;
        $this->contactemails = $paper->contactemails ?? '';
    }

    public function updatedContactemails(): void
    {
        $this->saved = false;
        $this->validationErrors = $this->doValidate();
    }

    /** テキストエリアの内容を行分割してメールアドレス配列に変換する */
    private function emlist(): array
    {
        $ema = explode("\n", trim($this->contactemails));
        $ema = array_map('trim', $ema);
        $ema = array_values(array_filter($ema, fn ($v) => $v !== ''));
        return $ema;
    }

    /** バリデーション実行。エラーメッセージ配列を返す（空なら正常）。 */
    private function doValidate(): array
    {
        $ema = $this->emlist();
        $max = (int) env('CONTACTEMAILS_MAX', 5);
        $errors = [];

        if (count($ema) === 0 || count($ema) > $max) {
            $errors[] = "投稿連絡用メールアドレスは1件以上{$max}件以内で入力してください。";
            return $errors;
        }

        $validator = Validator::make(
            ['ema' => $ema],
            ['ema.*' => 'required|email|max:255']
        );

        if ($validator->fails()) {
            $invalids = [];
            foreach ($validator->errors()->all() as $error) {
                if (preg_match('/\d+/', $error, $matches)) {
                    $invalids[] = $ema[(int) $matches[0]] ?? '';
                }
            }
            if (!empty($invalids)) {
                $errors[] = '【' . implode('】【', $invalids) . '】は有効なメールアドレスではありません。';
            }
        }

        return $errors;
    }

    public function save(): void
    {
        $this->validationErrors = $this->doValidate();

        if (!empty($this->validationErrors)) {
            return;
        }

        $em = implode("\n", $this->emlist());
        $this->paper->contactemails = $em;
        $this->paper->save();
        $this->paper->updateContacts();

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.contact-email-editor');
    }
}
