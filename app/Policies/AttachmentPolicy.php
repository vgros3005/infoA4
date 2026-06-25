<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttachmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $oUser): bool
    {
        return $oUser->is_active;
    }

    public function view(User $oUser, Attachment $oAttachment): bool
    {
        return $oUser->is_active;
    }

    public function create(User $oUser): bool
    {
        return $oUser->is_active;
    }

    public function delete(User $oUser, Attachment $oAttachment): bool
    {
        return $oUser->id === $oAttachment->uploaded_by;
    }
}
