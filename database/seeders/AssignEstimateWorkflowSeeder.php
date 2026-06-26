<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Status;
use App\Models\StatusAction;
use Illuminate\Database\Seeder;

/**
 * Inserts the "affectation" and "estimation" statuses between "validated"
 * and "in_development", and wires up their workflow actions.
 */
class AssignEstimateWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        // --- New statuses ---
        $aNewStatuses = [
            [
                'name'      => 'affectation',
                'label'     => 'En affectation',
                'color'     => 'indigo',
                'icon'      => 'bi-person-check',
                'sort_order' => 5,
            ],
            [
                'name'      => 'estimation',
                'label'     => 'En chiffrage',
                'color'     => 'teal',
                'icon'      => 'bi-calculator',
                'sort_order' => 6,
            ],
        ];

        foreach ($aNewStatuses as $aStatus) {
            Status::updateOrCreate(['name' => $aStatus['name']], $aStatus);
        }

        // --- Push existing statuses up to make room ---
        $aBumped = [
            'in_development'     => 7,
            'in_testing'         => 8,
            'pending_deployment' => 9,
            'deployed'           => 10,
            'rejected'           => 11,
            'on_hold'            => 12,
        ];
        foreach ($aBumped as $sName => $iOrder) {
            Status::where('name', $sName)->update(['sort_order' => $iOrder]);
        }

        // --- Roles ---
        $oRoleAdmin = Role::where('name', 'admin')->first();
        $oRolePM    = Role::where('name', 'project_manager')->first();
        $oRoleDev   = Role::where('name', 'developer')->first();

        $oStatusValidated    = Status::where('name', 'validated')->first();
        $oStatusAffectation  = Status::where('name', 'affectation')->first();
        $oStatusEstimation   = Status::where('name', 'estimation')->first();
        $oStatusInDev        = Status::where('name', 'in_development')->first();

        if (!$oStatusValidated || !$oStatusAffectation || !$oStatusEstimation || !$oStatusInDev) {
            $this->command->error('Missing base statuses — run StatusSeeder first.');
            return;
        }

        // Remove old direct validated → in_development action (replaced by the new chain)
        StatusAction::where('status_id', $oStatusValidated->id)
            ->where('action_name', 'start_dev')
            ->delete();

        // --- New workflow actions ---
        $aActions = [
            [
                'status_id'           => $oStatusValidated->id,
                'target_status_id'    => $oStatusAffectation->id,
                'action_label'        => 'Affecter',
                'action_name'         => 'assign',
                'button_color'        => 'indigo',
                'requires_comment'    => false,
                'requires_assignment' => true,
                'requires_estimation' => false,
                'sort_order'          => 10,
                'roles'               => [$oRoleAdmin, $oRolePM],
            ],
            [
                'status_id'           => $oStatusAffectation->id,
                'target_status_id'    => $oStatusEstimation->id,
                'action_label'        => 'Chiffrer',
                'action_name'         => 'estimate',
                'button_color'        => 'teal',
                'requires_comment'    => false,
                'requires_assignment' => false,
                'requires_estimation' => true,
                'sort_order'          => 10,
                'roles'               => [$oRoleAdmin, $oRolePM, $oRoleDev],
            ],
            [
                'status_id'           => $oStatusEstimation->id,
                'target_status_id'    => $oStatusInDev->id,
                'action_label'        => 'Démarrer le développement',
                'action_name'         => 'start_dev',
                'button_color'        => 'primary',
                'requires_comment'    => false,
                'requires_assignment' => false,
                'requires_estimation' => false,
                'sort_order'          => 10,
                'roles'               => [$oRoleAdmin, $oRolePM, $oRoleDev],
            ],
        ];

        foreach ($aActions as $aAction) {
            $aRoles = $aAction['roles'];
            unset($aAction['roles']);

            $oAction = StatusAction::updateOrCreate(
                ['status_id' => $aAction['status_id'], 'action_name' => $aAction['action_name']],
                $aAction
            );

            $oAction->roles()->sync(collect($aRoles)->filter()->pluck('id')->toArray());
        }
    }
}
