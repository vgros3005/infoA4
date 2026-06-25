<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $aRoles = [
            [
                'name' => 'admin', 'label' => 'Administrateur', 'color' => 'danger',
                'can_create_request' => true, 'can_validate_request' => true,
                'can_change_status' => true, 'can_assign_task' => true,
                'can_export_pdf' => true, 'can_admin' => true, 'sort_order' => 1,
            ],
            [
                'name' => 'project_manager', 'label' => 'Chef de projet', 'color' => 'primary',
                'can_create_request' => true, 'can_validate_request' => true,
                'can_change_status' => true, 'can_assign_task' => true,
                'can_export_pdf' => true, 'can_admin' => false, 'sort_order' => 2,
            ],
            [
                'name' => 'developer', 'label' => 'Développeur', 'color' => 'success',
                'can_create_request' => false, 'can_validate_request' => false,
                'can_change_status' => true, 'can_assign_task' => false,
                'can_export_pdf' => true, 'can_admin' => false, 'sort_order' => 3,
            ],
            [
                'name' => 'tester', 'label' => 'Testeur', 'color' => 'warning',
                'can_create_request' => false, 'can_validate_request' => false,
                'can_change_status' => true, 'can_assign_task' => false,
                'can_export_pdf' => true, 'can_admin' => false, 'sort_order' => 4,
            ],
            [
                'name' => 'requester', 'label' => 'Demandeur', 'color' => 'info',
                'can_create_request' => true, 'can_validate_request' => false,
                'can_change_status' => false, 'can_assign_task' => false,
                'can_export_pdf' => false, 'can_admin' => false, 'sort_order' => 5,
            ],
        ];

        foreach ($aRoles as $aRole) {
            Role::updateOrCreate(['name' => $aRole['name']], $aRole);
        }
    }
}
