<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Role extends Model
{
    use HasFactory;
    use FindByIdOrNameTrait; // Role::findByIdOrName(id数値でも nameでもよい)

    // 暫定ロール ここで設定したものはSeederで作成される。
    public static $roles = [
        'ec' => '編集長',
        'aec' => '幹事',
        'rev' => '査読者',
        'cm' => '編集委員',
        'admin' => '管理者',
        'manager' => 'マネージャ',
        'brev' => '査読者候補',
    ];

    protected $fillable = [
        'name',
        'desc'
    ];

    public function users(string $desc = 'users.id')
    {
        $tbl = 'role_user';
        return $this->belongsToMany(User::class, $tbl)->orderBy('users.id'); //->using(RolesUser::class);
    }
    public function users_desc(string $desc = 'users.id')
    {
        $tbl = 'role_user';
        return $this->belongsToMany(User::class, $tbl)->orderByDesc($desc); //->using(RolesUser::class);
    }

    public function users_except_paper_manager(int $paper_id)
    {
        $tbl = 'role_user';
        $paper = Paper::find($paper_id);
        $managers = $paper->managers()->pluck("users.id")->toArray();
        return $this->belongsToMany(User::class, $tbl)->whereNotIn("users.id", $managers)->orderBy('users.id');
    }

    public function uids()
    {
        // get user ids of this role
        $tbl = 'role_user';
        return $this->belongsToMany(User::class, $tbl)->pluck('user_id')->toArray();
    }

    public function containsUser(int $user_id): bool
    {
        return $this->users()->where("user_id", $user_id)->exists();
    }

    public static function checkRoleUser(string|int $role_id, int $user_id): bool
    {
        $role = null;
        if (is_integer($role_id)) {
            $role = Role::find($role_id);
        } else if (is_string($role_id)) {
            $role = Role::where("name", $role_id)->first();
        }
        if ($role === null) {
            return false;
        }
        return $role->containsUser($user_id);
    }

    /**
     * このRoleよりも、idが小さいRoleのnameを | でつないだもの
     * ただし、長さが0だったら、admin を追加する。
     */
    public function aboveRoles()
    {
        $roles = Role::orderBy("id")->get();
        $ary = [];
        foreach ($roles as $role) {
            if ($this->id >= $role->id) break;
            $ary[] = $role->name;
        }
        if (count($ary) == 0) $ary[] = "admin";
        return implode("|", $ary);
    }

    /**
     * テスト用：tinkerから呼び出す
     * demo = 10
     * meta|rev|aec|pub|award|acc|demo|web|wc|admin
     */
    public static function resetRolesExcept(int $user_id, string|array $roles): array
    {
        $user = User::find($user_id);
        if (is_string($roles)) {
            $roles = explode("|", $roles);
            $roles = Role::whereIn("name", $roles)->pluck("id")->toArray();
        }
        $user->roles()->detach();
        foreach ($roles as $role) {
            $user->roles()->attach(Role::find($role));
        }
        return $roles;
    }
    // テスト用：tinkerから呼び出す
    public static function setRolesExcept(int $user_id, string|array $roles): array
    {
        $user = User::find($user_id);
        if (is_string($roles)) {
            $roles = explode("|", $roles);
            $roles = Role::whereIn("name", $roles)->pluck("id")->toArray();
            info($roles);
        }
        $all = Role::pluck("id")->toArray();
        foreach ($all as $role) {
            $user->roles()->attach(Role::find($role));
        }
        foreach ($roles as $role) {
            $user->roles()->detach(Role::find($role));
        }
        return $user->roles()->pluck("id")->toArray();
    }
}
