<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Status;
use App\Models\StatusAction;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $aStatuses = [
            ['name' => 'draft', 'label' => 'Brouillon', 'color' => 'secondary', 'icon' => 'bi-file-earmark', 'is_initial' => true, 'sort_order' => 1],
            ['name' => 'submitted', 'label' => 'Soumise', 'color' => 'info', 'icon' => 'bi-send', 'sort_order' => 2],
            ['name' => 'pending_validation', 'label' => 'En attente de validation', 'color' => 'warning', 'icon' => 'bi-clock', 'sort_order' => 3],
            ['name' => 'validated', 'label' => 'Validée', 'color' => 'primary', 'icon' => 'bi-check-circle', 'freezes_request' => true, 'generates_pdf' => true, 'sort_order' => 4],
            ['name' => 'in_development', 'label' => 'En développement', 'color' => 'primary', 'icon' => 'bi-code-slash', 'sort_order' => 5],
            ['name' => 'in_testing', 'label' => 'En test', 'color' => 'warning', 'icon' => 'bi-bug', 'sort_order' => 6],
            ['name' => 'pending_deployment', 'label' => 'En attente de déploiement', 'color' => 'info', 'icon' => 'bi-cloud-upload', 'sort_order' => 7],
            ['name' => 'deployed', 'label' => 'Déployée en production', 'color' => 'success', 'icon' => 'bi-check2-all', 'is_final' => true, 'generates_pdf' => true, 'sort_order' => 8],
            ['name' => 'rejected', 'label' => 'Rejetée', 'color' => 'danger', 'icon' => 'bi-x-circle', 'is_final' => true, 'sort_order' => 9],
            ['name' => 'on_hold', 'label' => 'En suspens', 'color' => 'secondary', 'icon' => 'bi-pause-circle', 'sort_order' => 10],
        ];

        foreach ($aStatuses as $aStatus) {
            Status::updateOrCreate(['name' => $aStatus['name']], $aStatus);
        }

        $this->seedStatusActions();
    }

    private function seedStatusActions(): void
    {
        $oRoleAdmin = Role::where('name', 'admin')->first();
        $oRolePM    = Role::where('name', 'project_manager')->first();
        $oRoleDev   = Role::where('name', 'developer')->first();
        $oRoleTester = Role::where('name', 'tester')->first();
        $oRoleReq   = Role::where('name', 'requester')->first();

        $aActions = [
            ['from' => 'draft',              'to' => 'submitted',          'label' => 'Soumettre',       'name' => 'submit',     'color' => 'primary', 'roles' => [$oRoleReq, $oRoleAdmin, $oRolePM]],
            ['from' => 'submitted',          'to' => 'pending_validation', 'label' => 'Envoyer en validation', 'name' => 'send_validation', 'color' => 'info', 'roles' => [$oRoleAdmin, $oRolePM]],
            ['from' => 'pending_validation', 'to' => 'validated',          'label' => 'Valider',         'name' => 'validate',   'color' => 'success', 'roles' => [$oRoleAdmin, $oRolePM]],
            ['from' => 'pending_validation', 'to' => 'rejected',           'label' => 'Rejeter',         'name' => 'reject',     'color' => 'danger', 'requires_comment' => true, 'roles' => [$oRoleAdmin, $oRolePM]],
            ['from' => 'validated',          'to' => 'in_development',     'label' => 'Démarrer le dev', 'name' => 'start_dev',  'color' => 'primary', 'roles' => [$oRoleAdmin, $oRolePM, $oRoleDev]],
            ['from' => 'in_development',     'to' => 'in_testing',         'label' => 'Envoyer en test', 'name' => 'send_test',  'color' => 'warning', 'roles' => [$oRoleAdmin, $oRolePM, $oRoleDev]],
            ['from' => 'in_testing',         'to' => 'in_development',     'label' => 'Retour en dev',   'name' => 'back_dev',   'color' => 'secondary', 'requires_comment' => true, 'roles' => [$oRoleAdmin, $oRolePM, $oRoleTester]],
            ['from' => 'in_testing',         'to' => 'pending_deployment', 'label' => 'Tests OK',        'name' => 'tests_ok',   'color' => 'success', 'roles' => [$oRoleAdmin, $oRolePM, $oRoleTester]],
            ['from' => 'pending_deployment', 'to' => 'deployed',           'label' => 'Déployer en prod','name' => 'deploy',     'color' => 'success', 'roles' => [$oRoleAdmin, $oRolePM]],
            ['from' => 'in_development',     'to' => 'on_hold',            'label' => 'Mettre en suspens','name' => 'on_hold',   'color' => 'secondary', 'requires_comment' => true, 'roles' => [$oRoleAdmin, $oRolePM]],
            ['from' => 'on_hold',            'to' => 'in_development',     'label' => 'Reprendre',       'name' => 'resume',     'color' => 'primary', 'roles' => [$oRoleAdmin, $oRolePM]],
        ];

        foreach ($aActions as $iSort => $aAction) {
            $oFromStatus = Status::where('name', $aAction['from'])->first();
            $oToStatus   = Status::where('name', $aAction['to'])->first();

            if (!$oFromStatus || !$oToStatus) {
                continue;
            }

            $oStatusAction = StatusAction::updateOrCreate(
                ['status_id' => $oFromStatus->id, 'action_name' => $aAction['name']],
                [
                    'target_status_id' => $oToStatus->id,
                    'action_label'     => $aAction['label'],
                    'button_color'     => $aAction['color'],
                    'requires_comment' => $aAction['requires_comment'] ?? false,
                    'is_active'        => true,
                    'sort_order'       => $iSort,
                ]
            );

            $aRoleIds = collect($aAction['roles'])->filter()->pluck('id')->toArray();
            $oStatusAction->roles()->sync($aRoleIds);
        }
    }
}
