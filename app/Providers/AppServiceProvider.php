<?php

namespace App\Providers;

use App\Models\Attachment;
use App\Models\RequestA4;
use App\Models\Task;
use App\Models\TaskTimeEntry;
use App\Models\User;
use App\Policies\AttachmentPolicy;
use App\Policies\RequestA4Policy;
use App\Policies\TaskPolicy;
use App\Policies\TaskTimeEntryPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Super-admin bypass: admins pass every Gate/Policy check
        Gate::before(function (User $oUser, string $sAbility): ?bool {
            if ($oUser->isAdmin()) {
                return true;
            }
            return null;
        });

        Gate::policy(RequestA4::class, RequestA4Policy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(TaskTimeEntry::class, TaskTimeEntryPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);

        // Define admin-required gates for admin controllers
        Gate::define('admin', fn(User $oUser) => $oUser->isAdmin());
        Gate::define('manage-users', fn(User $oUser) => $oUser->isAdmin());
        Gate::define('manage-teams', fn(User $oUser) => $oUser->isAdmin() || $oUser->hasRole('chef_projet'));
    }
}
