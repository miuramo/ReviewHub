<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\LogModify;
use App\Models\Paper;
use App\Models\Role;
use App\Policies\FilePolicy;
use App\Policies\LogAccessPolicy;
use App\Policies\LogModifyPolicy;
use App\Policies\PaperPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Paper::class => PaperPolicy::class,
        File::class => FilePolicy::class,
        LogModify::class => LogModifyPolicy::class,
        LogAccess::class => LogAccessPolicy::class,
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin', function ($user) {
            $admin = Role::firstOrCreate(
                [
                    'name' => 'admin',
                ]
            );
            return $admin->users()->where("user_id", $user->id)->exists();
        });

        /**
         * $role_id は id数値でも nameでもよい。なんならObjectでもよい
         */
        Gate::define('role', function ($user, $role_id) {
            $role = Role::findByIdOrName($role_id);
            if ($role == null) return false;
            return $role->users()->where("user_id", $user->id)->exists();
        });
        /**
         * どれか1つのRole
         */
        Gate::define('role_any', function ($user, string $roles_str) {
            $roles = explode("|", $roles_str);

            // 1つ1つチェックして、どれかOKならtrueを返す。
            foreach ($roles as $role_id) {
                if ($user->can('role', $role_id)) return true;
            }
            return false;
        });


        Gate::define('edit_paper', function ($user, $paper) {
            if ($paper->owner === $user->id) {
                $ret = "user uid{$user->id} is owner of pid{$paper->id}";
            } else {
                $ret = "NOT ALLOWED user uid{$user->id} is not owner of pid{$paper->id} powner{$paper->owner}";
            }
            return ($paper->owner === $user->id);
        });

        Gate::define('show_paper', function ($user, $paper) {
            if ($paper->owner === $user->id) $ret = "user uid{$user->id} is owner of pid{$paper->id}";
            else if ($paper->isCoAuthorEmail($user->email)) {
                $ret = "user uid{$user->id} is coauthor of pid{$paper->id}";
            } else {
                $ret = "NOT ALLOWED: show_paper";
            }
            if ($paper->owner === $user->id) return true;
            return $paper->isCoAuthorEmail($user->email);
        });

        /**
         * カテゴリの管理権限
         */
        Gate::define('manage_paper', function ($user, $paper) {
            // もし、編集長なら、true
            if ($user->can('role', 'ec')) return true;
            // もし、著者なら、true
            if ($paper->isCoAuthorEmail($user->email)) return true;
            return false;
        });
        Gate::define('review_paper', function ($user, $paper) {
            // もし、編集長なら、true
            if ($user->can('role', 'ec')) return true;
            // もし、査読者またはメタなら、true
            $paper = Paper::find($paper);
            if ($paper->isReviewer($user->id)) return true;
            return false;
        });
        Gate::define('manage_cat_any', function ($user) {
            $catid_roles = Role::where('cat_id', '>', 0)->get();
            foreach ($catid_roles as $role) {
                // いずれかのRoleに所属していれば、true
                if ($role->containsUser($user->id)) return true;
            }
            return false;
        });

        /**
         * 査読管理 TODO: 一旦managerが設定されたら、それに従う。
         */
        Gate::define('manage_review', function ($user, $paper) {
            $paper = Paper::find($paper);
            if ($paper->managers()->count() > 0) {
                return $paper->isManager($user->id);
            } else {
                // もし、編集長なら、true
                if ($user->can('role', 'ec')) return true;
            }
            return false;
        });

        Gate::define('has_managed_papers', function ($user) {
            return ($user->managed_papers()->count() > 0);
        });

        Gate::define('manage_papermanager', function ($user, $paper) {
            // システムマネージャで、かつ、現在当該論文のペーパーマネージャであること。
            if (is_integer($paper)) {
                $paper = Paper::find($paper);
            }
            if ($user->can('role', 'manager') && $paper->isManager($user->id)) {
                return true;
            }
            return false;
        });

        /**
         * 編集委員なら、査読内容を見れる。
         */
        Gate::define('see_review', function ($user, $paper) {
            $paper = Paper::find($paper);
            // もし、編集長または編集委員なら、true
            // TODO: 利害関係者も外す
            if ($paper->isAuthorOrCoAuthor($user)) return false;
            if ($user->can('role_any', 'ec|cm')) return true;
            // TODO: 有効なTermがある場合も、見れる。
            // Termの期間と、投稿判定の期間が重なっているか。
            return false;
        });
    }
}
