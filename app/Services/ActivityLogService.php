<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Record an activity log entry.
     *
     * @param  string                                $sAction       Short action key (e.g. 'created', 'updated', 'deleted', 'status_changed').
     * @param  string                                $sDescription  Human-readable description of what happened.
     * @param  \Illuminate\Database\Eloquent\Model|null  $oModel    The model instance being acted upon (polymorphic).
     * @param  array<string, mixed>                  $aOldValues   Previous field values (for updates).
     * @param  array<string, mixed>                  $aNewValues   New field values (for updates/creates).
     * @return \App\Models\ActivityLog
     */
    public function log(
        string  $sAction,
        string  $sDescription,
        ?Model  $oModel    = null,
        array   $aOldValues = [],
        array   $aNewValues = []
    ): ActivityLog {
        // Sanitize sensitive fields before persisting
        $aSanitizedOld = $this->sanitize($aOldValues);
        $aSanitizedNew = $this->sanitize($aNewValues);

        $aPayload = [
            'user_id'     => Auth::id(),
            'action'      => $sAction,
            'description' => $sDescription,
            'old_values'  => $aSanitizedOld ?: null,
            'new_values'  => $aSanitizedNew ?: null,
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::userAgent(),
        ];

        if ($oModel !== null) {
            $aPayload['loggable_type'] = get_class($oModel);
            $aPayload['loggable_id']   = $oModel->getKey();
        }

        return ActivityLog::create($aPayload);
    }

    /**
     * Remove sensitive keys from a value array before persisting to the log.
     *
     * @param  array<string, mixed>  $aValues
     * @return array<string, mixed>
     */
    private function sanitize(array $aValues): array
    {
        $aSensitiveKeys = ['password', 'remember_token', 'api_token', 'secret'];

        return array_diff_key($aValues, array_flip($aSensitiveKeys));
    }
}
