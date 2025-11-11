<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use STS\ZipStream\Builder;
use STS\ZipStream\Models\File as ZipFile;
use ZipArchive;

/**
 * Class Paper
 *
 * @property int $id
 * @property int $category_id
 * @property string|null $title
 * @property string|null $contactemails
 * @property string|null $etitle
 * @property string|null $kwd
 * @property string|null $ekwd
 * @property string|null $abst
 * @property string|null $eabst
 * @property string|null $zipcode
 * @property string|null $address
 * @property string|null $telnum
 * @property string|null $faxnum
 * @property int $registid
 * @property string|null $discussagenda
 * @property bool $nopublishcatalog
 * @property string|null $remarks
 * @property int $numauthor
 * @property bool $authorchecked
 * @property bool $demoifaccepted
 * @property bool $demoifrejected
 * @property bool $donotwantshortaccept
 * @property int $finalizecount
 * @property Carbon|null $created_at
 * @property Carbon|null $modified_at
 * @property int $owner
 * @property Carbon|null $deleted_at
 *
 * @package App\Models
 */
class Paper extends Model
{
    use HasFactory;
    use SoftDeletes;
    // protected $table = 'papers';
    // public $timestamps = false;

    protected $with = ['currentstatus', 'currentsubmit', 'category', 'contacts', 'paperowner', 'submits', 'pdf_file', 'enqans', 'managers'];

    protected $casts = [
        'category_id' => 'int',
        'owner' => 'int',
        'registid' => 'int',
        'demoifaccepted' => 'bool',
        'nopublishcatalog' => 'bool',
        'pdf_file_id' => 'int',
        'locked' => 'bool',
        'status' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        // 'deleted' => 'bool',
        'maydirty' => 'json',
    ];

    protected $fillable = [
        'category_id',
        'owner',
        'authorlist',
        'title',
        'abst',
        'keyword',
        'etitle',
        'eabst',
        'ekeyword',
        'maydirty',
        'contactemails',
        'demoifaccepted',
        'nopublishcatalog',
        'remarks',
        'pdf_file_id',
        'zipcode',
        'address',
        'telnum',
        'registid',
        'locked',
        'history',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
        'status_id',
    ];


    public static function mandatory_bibs()
    {
        $koumoku = [
            'title' => '和文題名',
            'abst' => '和文アブストラクト',
            'keyword' => '和文キーワード',
            'authorlist' => '和文著者名(所属)',
            'etitle' => '英文題名',
            'eabst' => '英文Abstract',
            'ekeyword' => '英文Keyword',
            'eauthorlist' => '英文著者名(所属)'
        ];
        $skip_bibinfo = Setting::getval("SKIP_BIBINFO");
        $skip_bibinfo = json_decode($skip_bibinfo);
        foreach ($skip_bibinfo as $key) {
            unset($koumoku[$key]);
        }
        return $koumoku;
    }

    public function addFilesToZip(ZipArchive $zip, array $filetypes)
    {
        $count = 0;
        foreach ($filetypes as $ft) {
            $fti = "{$ft}_file_id"; // file_id for $ft (filetype)
            $file = File::find($this->{$fti});
            if ($file == null) continue;
            $zip->addFile($file->fullpath(), $this->id_03d() . "_{$ft}." . $file->extension());
            $count++;
        }
        return $count;
    }
    // https://github.com/stechstudio/laravel-zipstream を使用
    public function addFilesToZipStream(Builder $zip, array $filetypes)
    {
        $count = 0;
        foreach ($filetypes as $ft) {
            $fti = "{$ft}_file_id"; // file_id for $ft (filetype)
            $file = File::find($this->{$fti});
            if ($file == null) continue;
            $zip->add(ZipFile::make($file->fullpath(), $this->id_03d() . "_{$ft}." . $file->extension()));
            $count++;
        }
        return $count;
    }
    // こちらも https://github.com/stechstudio/laravel-zipstream を使用
    public function addFilesToZip_ForPub(Builder $zip, array $filetypes, string $fn_prefix, string $fn)
    {
        $count = 0;
        foreach ($filetypes as $ftid) {
            // $fti = "{$ft}_file_id"; // file_id for $ft (filetype)
            $file = File::where('paper_id', $this->id)->where("filetype_id", $ftid)->where('valid', 1)->where('deleted', 0)->where('archived', 0)->first();
            if ($file == null) continue;
            $zip->add(ZipFile::make($file->fullpath(), $fn_prefix . $fn . "." . $file->extension()));
            $count++;
        }
        return $count;
    }


    public function files()
    {
        // return $this->hasMany(File::class, 'paper_id');
        return $this->hasMany(File::class, 'paper_id')->where('valid', 1)->where('deleted', 0);
    }
    public function archiveFiles()
    {
        foreach ($this->files as $file) {
            if ($file) {
                $file->locked = true;
                $file->archived = true; // アーカイブフラグを立てる
                // $file->deleted = true;
                $file->save();
            }
        }
    }

    public function managers()
    {
        return $this->belongsToMany(User::class, 'paper_manager');
    }

    public function currentstatus()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    // 最新のSubmitを返す
    public function currentsubmit()
    {
        return $this->hasOne(Submit::class)->latest();
        // return $this->hasOne(Submit::class)->where('round', 1);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function contacts()
    {
        // $table_fields = Schema::getColumnListing('paper_contact');
        return $this->belongsToMany(Contact::class, 'paper_contact'); // ->withPivot($table_fields)->using(PapersUser::class);
    }
    // $p = Paper::find(1)
    //   ->contacts()
    //   ->attach(5);　で、Contact.id = 5 を追加する。反対はdetach,配列も可。
    // syncで、削除＆追加をする。
    public function paperowner()
    {
        return $this->belongsTo(User::class, 'owner');
    }

    public function submits()
    {
        return $this->hasMany(Submit::class)->orderBy('round', 'desc');
    }
    public function submits_desc()
    {
        return $this->hasMany(Submit::class)->orderBy('round', 'desc');
    }

    public function id_03d()
    {
        return sprintf("%04d", $this->id);
    }
    public function pdf_file()
    {
        return $this->belongsTo(File::class, 'pdf_file_id');
    }
    public function answer_file()
    {
        $answer_file = File::where('paper_id', $this->id)
            ->where('filetype_id', 2) // Answer file
            ->orderBy('created_at', 'desc')
            ->where('valid', 1)
            ->where('deleted', 0)
            ->where('pending', 0)
            ->first();
        return $answer_file;
    }
    /**
     * 過去の投稿ファイル
     */
    public function past_pdf_files()
    {
        return File::where('paper_id', $this->id)
            ->where('filetype_id', 1) // PDF file
            ->where('valid', 1)
            ->where('deleted', 0)
            ->where('pending', 0)
            ->where('archived', 1)
            ->orderBy('created_at', 'desc')->get();
    }
    public function enqans()
    {
        return $this->hasMany(EnqueteAnswer::class, 'paper_id');
    }
    public function enqansByItemId($enq_itm_id)
    {
        return null;
    }

    public function isReviewer(int $uid)
    {
        $submit = $this->currentsubmit;
        if ($submit == null) return false;
        if ($submit->reviews()->where('user_id', $uid)->exists()) return true;
        if ($submit->aec_id == $uid) return true;
        // if ($submit->meta()->user_id == $uid) return true;
        // if ($submit->rev1()->user_id == $uid) return true;
        // if ($submit->rev2()->user_id == $uid) return true;
        // if ($submit->rev3()->user_id == $uid) return true;
        return false;
    }
    public function isManager(int $uid)
    {
        return $this->managers()->where('user_id', $uid)->exists();
    }

    /**
     * この論文の査読結果のトークンを生成（著者がみえる査読結果）
     */
    public function token()
    {
        return sha1($this->id . $this->user_id . $this->paper_id . $this->category_id . $this->title);
    }

    /**
     * 2回目以降のラウンドのSubmitを作成する (1回目はPaper作成時に,
     * PaperObserverで作成される)
     * 採録通知のときに、作成する。著者は2回目以降は、Submitに対してファイルをアップロードする。
     */
    public function createSubmit(int $round)
    {
        $sub = new Submit();
        $sub->paper_id = $this->id;
        $sub->category_id = $this->category_id;
        $sub->round = $round;
        $sub->save();
    }

    /**
     * getAuthorType
     */
    public function getAuthorType(): int
    {
        if ($this->owner == Auth::user()->id) {
            return 1; // main author
        } else if ($this->isCoAuthorEmail(Auth::user()->email)) {
            return 2; // coauthor
        }
        return -1;
    }
    public static function getAT($uid, $pid): int
    {
        $p = Paper::find($pid);
        $u = User::find($uid);
        if ($p->owner == $uid) {
            return 1; // main author
        } else if ($p->isCoAuthorEmail($u->email)) {
            return 2; // coauthor
        }
        return -1;
    }

    /**
     * contactemails から、Contactを作成する
     * ここは、contactemailsが変更されたら、かならず実行する。
     *
     * （投稿者アカウントのメールが変更されたら、どうする？＞基本的に、すべてのPaperについて、ここを実行すればよいが、もうすこし省力化できるかも）
     */
    public function updateContacts()
    {
        $this->contacts()->detach(); // 既存のはすべて削除する
        //contactemails から、Contactを作成する（重複はtableのunique制約で保証される）
        $ema = explode("\n", trim($this->contactemails));
        foreach ($ema as $e) {
            DB::transaction(function () use ($e) {
                $con = Contact::firstOrCreate([
                    'email' => $e,
                ]);
                if ($con->infoprovider == null) {
                    $con->infoprovider = $this->owner;
                    $con->save();
                }
                $this->contacts()->attach($con->id);
            });
        }
        $this->refresh();
    }
    // 主にテスト用。現在のContactリレーションからcontactemailsを逆に生成する。
    public function updateContactemailsFromContacts()
    {
        // ここで、いったん$this->contactsを再読み込みする必要があるらしい。
        $this->refresh();
        $cmlist = [];
        foreach ($this->contacts as $c) {
            $cmlist[] = $c->email;
        }
        $this->contactemails = implode("\n", $cmlist);
        $this->save();
        return $cmlist;
    }

    // contactemailsから抜く
    public function remove_contact(Contact $con)
    {
        $this->contacts()->detach($con->id);
        // ここで、いったんcontactsを再読み込みする必要があるらしい。
        $this->updateContactemailsFromContacts();
        $this->refresh();
    }
    // contactemailsに足す
    public function add_contact(Contact $con)
    {
        $this->contacts()->attach($con->id);
        $this->updateContactemailsFromContacts();
        $this->refresh();
    }
    public function add_contactemail(string $em)
    {
        $ema = explode("\n", trim($this->contactemails));
        $ema[] = $em;
        $this->contactemails = implode("\n", $ema);
        $this->save();
        $this->updateContacts();
    }

    public function get_mail_to_cc()
    {
        $cclist = [];
        $bcclist = [];
        foreach ($this->contacts as $con) {
            $cclist[] = $con->email;
        }
        if ($this->bcc_contactemails != null) {
            $bccs = explode("\n", trim($this->bcc_contactemails));
            foreach ($bccs as $bcc) {
                $bcclist[] = $bcc;
            }
        }
        return ["to" => $this->paperowner->email, "cc" => $cclist, "bcc" => $bcclist];
    }
    public function get_mail_manager()
    {
        return $this->managers->pluck("email")->toArray();
    }

    /**
     * 共著者ならtrue
     */
    public function isCoAuthorEmail(string $em): bool
    {
        // 以前は、地道にやっていたが、時間がかかるので
        // $ema = explode("\n", trim($em));
        // $ema = array_map("trim", $ema);
        // foreach ($ema as $e) {
        //     if ($e == $em) return true;
        // }
        // return false;
        // Contactのリレーションを利用する方法にする
        try {
            $contact = Contact::where('email', $em)->firstOrFail();
            // if ($contact == null) return false; // なくてもよさそう
            return $this->contacts()->where("contact_id", $contact->id)->exists();
        } catch (ModelNotFoundException $ex) {
            return false;
        }
    }

    // public function submits()
    // {
    //     return $this->hasMany(Submit::class);
    // }

    public function delete_me()
    {
        $this->contacts()->detach(); //belongsToManyリレーションを削除する
        Paper::destroy($this->id);
    }

    public function softdelete_me()
    {
        $this->delete();
    }

    /**
     * 投稿ファイルのバリデーション（注：投稿可能期間のみ有効）
     */
    public function validateFiles()
    {
        // ルール； 論文(1)、回答書(2)、対照表(3)、その他(4) について、4以外は1つのみ。4は0個以上。

        $checkary = [];
        $errorary = [];
        $cat = Category::find($this->category_id);
        if ($cat == null) return []; //通常はありえないが、テストを通すため...
        foreach ($this->files as $file) {
            // if ($file->mime == "application/pdf") {
            if ($file->deleted) continue;
            if ($file->pending) continue;
            if ($file->archived) continue; // アーカイブされたファイルは無視する
            $checkary[$file->filetype_id][] = $file->id;
            // }
        }
        // それぞれのファイルの数をチェックする
        if (!isset($checkary[1]) || count($checkary[1]) == 0) {
            $errorary[] = "論文PDFは必須です。";
        } else if (count($checkary[1]) > 1) {
            $errorary[] = "論文PDFは1つのファイルのみ受け付けます。";
        }
        if (isset($checkary[2]) && count($checkary[2]) > 1) {
            $errorary[] = "回答書は1つのファイルのみ受け付けます。";
        }
        if (isset($checkary[3]) && count($checkary[3]) > 1) {
            $errorary[] = "対照表は1つのファイルのみ受け付けます。";
        }

        if (count($errorary) > 0) return $errorary;

        // ALL OKなら、paperにセットする
        $this->pdf_file_id = $checkary[1][0];
        $this->img_file_id = isset($checkary[2][0]) ? $checkary[2][0] : null;
        $this->video_file_id = isset($checkary[3][0]) ? $checkary[3][0] : null;
        $this->altpdf_file_id = isset($checkary[4][0]) ? $checkary[4][0] : null;
        $this->save();
        return [];
    }

    /**
     * PDFファイルがなければ true (@MailTemplate mt_nofile)
     */
    public function check_nofile()
    {
        if ($this->pdf_file_id == null) return true;
        // もし、pdf_file_id が無効なら、もう一度validateする。
        if ($this->pdf_file()->deleted) {
            $this->validateFiles();
            $this->refresh(); // validate reload
            if ($this->pdf_file()->deleted) {
                return true;
            }
        }
        return false;
    }

    /**
     * 書誌情報のチェック。足りないものを配列で返す。
     */
    public function validateBibinfo()
    {
        // 何が必須か？は、全部から、SKIP_BIBINFOを引く。
        // $manda = ["title", "etitle", "authorlist", "eauthorlist", "abst", "eabst", "keyword", "ekeyword"];
        // 書誌情報の設定項目
        $koumoku = Paper::mandatory_bibs();
        // 設定されていないものがあれば、error配列として返す。
        $errors = [];
        foreach ($koumoku as $key => $expr) {
            if ($this->{$key} == null || strlen($this->{$key}) < 2) {
                $errors[$key] = "書誌情報の設定から、" . $expr . " を入力してください。";
            }
        }

        // 著者名(所属) のチェック
        foreach ($koumoku as $key => $expr) {
            if ($key == "authorlist" || $key == "eauthorlist") {
                $ret = $this->authorlist_check($key);
                if (!$ret) {
                    $errors[$key] = ($key == "authorlist" ? "和文著者名(所属)" : "英文Authors(所属)") . " の書式が正しくありません。";
                }
            }
        }
        return $errors;
    }


    public function between(int $s, int $x, int $e)
    {
        return ($s <= $x && $x <= $e);
    }

    /**
     * PdfJob => File(2ページ以上のとき) =>　ここでタイトル設定・更新
     */
    public function extractTitleAndAuthors(string $text)
    {
        // もし、カテゴリの投稿受付設定 extract_title が　0　だったら、実行しない。
        $cat = Category::find($this->category_id);
        if (!$cat->extract_title) {
            // info("note: category->extract_title is 0. SKIPPING.");
            return;
        }
        if ($this->locked) {
            return;
        }

        // 下処理として、改行をとりのぞく
        if (function_exists("mb_strpos")) {
            $nocr_text = str_replace(["\r", "\n"], "", $text);
            $first_author_name = trim($this->paperowner->name);
            $pos = mb_strpos($nocr_text, $first_author_name);
            if ($pos !== false) {
                $title_candidate = mb_substr($nocr_text, 0, $pos);
            } else {
                // みつからなかったので、
                $title_candidate = mb_substr($nocr_text, 0, 120);
            }
        } else {
            $nocr_text = str_replace("\n", "", $text);
            $first_author_name = $this->paperowner->name;
            $pos = strpos($nocr_text, $first_author_name);
            if ($pos > -1) {
                $title_candidate = substr($nocr_text, 0, $pos);
            } else {
                // みつからなかったので、
                $title_candidate = substr($nocr_text, 0, 120) . "...";
            }
        }
        // SKIP_HEAD_n を適用する。（先頭にあれば、削除する
        $sets = Setting::where("name", "like", "SKIP_HEAD_%")->where("valid", true)->get();
        foreach ($sets as $set) {
            $title_candidate = str_replace($set->value, "", $title_candidate);
        }
        $this->title = $title_candidate;
        $this->save();
    }

    public function demo_ifaccepted()
    {
        $demoenqitem = EnqueteItem::where("name", "demoifaccepted")->first();
        if ($demoenqitem != null) {
            $demoenqitemid = $demoenqitem->id;
            $ans = $this->enqans->where("enquete_item_id", $demoenqitemid)->first();
            if ($ans != null && $ans->valuestr == "はい") {
                return true;
            }
        }
        return false;
    }

    public function validate_accepted()
    {
        //ファイルエラー
        $fileerrors = $this->validateFiles();
        // アンケートエラー
        $enqerrors = Enquete::validateEnquetes($this);

        $this->accepted = (count($fileerrors) == 0 && count($enqerrors) == 0);
        $this->save();
    }

    /**
     * 著者名(所属) のチェック
     */
    public function authorlist_check($field = "authorlist")
    {
        $src = $this->{$field};
        $src = str_replace("（", "(", $src);
        $src = str_replace("）", ")", $src);
        $lines = explode("\n", $src);
        $lines = array_map("trim", $lines);
        if (count($lines) == 0) return true;
        $pattern = '/^([\p{Hiragana}\p{Katakana}\p{Han}\w\-,.]+(?:\s[\p{Hiragana}\p{Katakana}\p{Han}\w\-,.]+)*)\s*\([^\)]+\)$/u';
        foreach ($lines as $line) {
            if (!preg_match($pattern, $line)) {
                return false;
            }
        }
        return true;
    }
    /**
     * 基本ルール(pre)は適用する。
     */
    public function apply_affil_fix($affil, bool $pre_apply = true, bool $use_short = false)
    {
        // 事前適用ルールを取得
        if ($pre_apply) {
            $pre_rules = Affil::where('pre', true)->where('skip', false)->get();
            foreach ($pre_rules as $rule) {
                $affil = str_replace($rule->before, $rule->after, $affil);
            }
        }
        $afary = explode("/", $affil);
        $afary = array_map('trim', $afary);

        $ret = [];
        foreach ($afary as $af) {
            // 半角スペースを削除
            $af = str_replace(" ", "", $af);
            // Affilテーブルを参照して、変換する
            $obj = Affil::where('before', $af)->where('skip', false)->first();
            if ($obj != null && $use_short) {
                $ret[] = $obj->after;
            } else {
                $ret[] = $af;
            }
        }
        //配列retの要素が空文字列なら、要素を削除する
        $ret = array_filter($ret, function ($v) {
            return strlen($v) > 0;
        });
        $ret = array_map(function ($v) {
            return $this->remove_hankaku_between_zenkaku($v); // $this->remove_hankaku_between_zenkaku($v);
        }, $ret);
        return implode("/", $ret);
    }


    // 著者名と所属のパース結果を配列で返す。英文所属は引数にeauthorlist を指定する。
    public function authorlist_ary($field = "authorlist", bool $use_short = false)
    {
        $ret = [];
        // まず、カッコをおきかえる
        if (!isset($this->{$field})) return $ret; // 著者名と所属が設定されてない
        $lines = explode("\n", $this->{$field});
        $lines = array_map("trim", $lines);
        foreach ($lines as $line) {
            $line = str_replace("（", "(", $line);
            $line = str_replace("）", ")", $line);
            $line = str_replace("(", "\t", $line);
            $line = str_replace(")", "\t", $line);
            $ary = explode("\t", trim($line));
            $ary = array_map("trim", $ary);
            // ここまでで、ary[0]には氏名、ary[1]には所属がはいる
            if (isset($ary[1])) {
                $ary[1] = str_replace(" ","", $ary[1]);
                $ary[1] = $this->apply_affil_fix($ary[1], true, $use_short);
            }
            $ret[] = $ary;
        }
        return $ret;
    }
    public function rorlist_ary()
    {
        $ret = [];
        $lines = explode("\n", $this->ror);
        $lines = array_map("trim", $lines);
        foreach ($lines as $line) {
            $ary = explode(" ", trim($line));
            $ary = array_map("trim", $ary);
            // ここまでで、ary[0]には所属、ary[1]にはRORがはいる
            if (isset($ary[1])) {
                $ary[1] = trim($ary[1]);
            }
            $ret[] = $ary;
        }
        return $ret;
    }
    // 所属の修正を適用する
    public function getAllAffils($idx = 1, $prefix = "", bool $use_short = true)
    {
        $ary = $this->authorlist_ary($prefix . "authorlist", $use_short);
        $ret = [];
        foreach ($ary as $a) {
            if (isset($a[$idx])) $ret[] = $a[$idx];
        }
        return implode(";;", $ret);
    }
    public static function remove_hankaku_between_zenkaku(string $val): string
    {
        $val = preg_replace('/([一-龥ぁ-ゔァ-ヴー々〆〤．，。、Ａ-Ｚａ-ｚ０-９])\s+([a-zA-Z0-9Ａ-Ｚａ-ｚ０-９一-龥ぁ-ゔァ-ヴー々〆〤．，。、])/u', '$1$2', $val);
        $val = preg_replace('/([a-zA-Z0-9Ａ-Ｚａ-ｚ０-９])\s+([一-龥ぁ-ゔァ-ヴー々〆〤．，。、Ａ-Ｚａ-ｚ０-９])/u', '$1$2', $val);
        $pattern = '/(?<=\p{Han})\s(?=\p{ASCII})|(?<=\p{ASCII})\s(?=\p{Han})/u';
        $val = preg_replace($pattern, '', $val);

        return $val;
    }


    /**
     * 配列をかえす
     */
    public function bibinfo(bool $use_short = false)
    {
        $ret = [];
        $ret['title'] = $this->title;
        $ret['authors'] = [];
        $ret['affils'] = [];
        foreach ($this->authorlist_ary("authorlist", $use_short) as $uu) {
            $ret['authors'][] = $uu[0];
            if (!isset($uu[1])) $fixed_affil = "未設定";
            else
                $fixed_affil = $uu[1];

            $fixed_affil = $this->apply_affil_fix($fixed_affil, true, $use_short);

            $ret['affils'][] = $fixed_affil;
        }
        return $ret;
    }

    /**
     * 著者名、文字列をかえす
     * abbr 連続する著者の所属を省略する
     */
    public function bibauthors(bool $abbr = false, bool $use_short = false, string $field = "authorlist")
    {
        $name = [];
        $affil = [];
        $count = 0;
        foreach ($this->authorlist_ary($field, $use_short) as $uu) {
            $name[] = $uu[0];
            $affil[] = (isset($uu[1])) ? $uu[1] : ""; //そもそも所属がなければ、空にせざるを得ない
            $count++;
        }
        if ($abbr) {
            for ($i = 0; $i < $count; $i++) {
                if ($i < ($count - 1) && $affil[$i] == $affil[$i + 1]) {
                    $affil[$i] = ""; // 重複しており、最後より1つ前なら、省略するため空にする。
                }
            }
        }
        $ret = [];
        for ($i = 0; $i < $count; $i++) {
            if (strlen($affil[$i]) > 0) { // 所属が空じゃなければ、（）で表示する
                $ret[] = $name[$i] . " (" . $affil[$i] . ")";
            } else {
                $ret[] = $name[$i];
            }
        }
        return implode("，", $ret); // カンマでつなげて出力
    }

    public function writeHintFile()
    {
        $txt = "pdf_file_id\t" . $this->pdf_file_id . "\n";
        $txt .= "title\t" . $this->title . "\n";
        $txt .= "titletail\t" . $this->titletail . "\n";
        $txt .= "authorhead\t" . $this->authorhead . "\n";
        $txt .= "updated\t" . date("Y-m-d_H:i:s") . "\n";

        $this->pdf_file->writeHintFile($txt);
    }

    public function pdftotext()
    {
        if ($this->pdf_file)
            return $this->pdf_file->getPdfText();
        return "(pdftotext準備中)";
    }
    public function title_candidate()
    {
        $title = str_replace("\n", "", $this->pdftotext());
        // owner name
        $owner = $this->paperowner->name;
        $pos1 = mb_strpos($title, $owner);
        if ($pos1 > -1) {
            $title = mb_substr($title, 0, $pos1);
        }
        return $title;
    }

    public function lockMe(bool $b)
    {
        $this->locked = $b;
        $this->save();
    }
    public function lockAll(bool $b)
    {
        // 現在アップロードされているすべてのファイル（削除済みを除く）をロックする
        foreach ($this->files as $file) {
            if (!$file->deleted) {
                $file->locked = $b;
                $file->save();
            }
        }
    }
    public function archiveAll(bool $b)
    {
        // 現在アップロードされているすべてのファイル（削除済みを除く）をアーカイブする
        foreach ($this->files as $file) {
            if (!$file->deleted) {
                $file->archived = $b;
                $file->save();
            }
        }
    }

    /**
     * 初期状態のマネージャを設定する
     */
    public function setDefaultManagers()
    {
        $role = Role::findByIdOrName("ec");
        foreach ($role->users as $user) {
            if ($user->id == $this->owner) continue;
            $this->managers()->attach($user->id);
        }
    }

    /**
     * RORを取得して、rorフィールドにセットする
     */
    public function fetchRor()
    {
        $alist = $this->authorlist_ary("authorlist", true);
        $ror_lines = [];
        foreach ($alist as $af) {
            $afary = explode("/", $af[1]);
            foreach($afary as $a) {
                $a = trim($a);
                $ror_id = Ror::getRor($a);
                if ($ror_id != null) {
                    $ror_lines[] = $a . " " . $ror_id;
                }
            }
        }
        $this->ror = implode("\n", $ror_lines);
        $this->save();
        return $this->ror;
    }


    /**
     * 投稿日・最終採択日
     */
    public function get_important_dates_display()
    {
        // 関連Submit をすべて取得
        $firstsub = $this->submits()->where('round', 1)->first();
        $lastsub = $this->submits()->orderBy('round', 'desc')->first();
        $dates = [];
        if ($firstsub) {
            $dates[] = "（" . $this->format_ymd($firstsub->submitted_at) . "受付）";
        }
        if ($lastsub && $lastsub->ec_decision_at != null) {
            $dates[] = "（" . $this->format_ymd($lastsub->ec_decision_at) . "採録）";
        }


        return implode("<br>", $dates);
    }
    public function format_ymd($dt)
    {
        if ($dt == null) return "";
        return date("Y年 m月 d日", strtotime($dt));
    }
}
