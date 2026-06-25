<?php

namespace Database\Seeders;

use App\Models\RequestType;
use Illuminate\Database\Seeder;

class RequestTypeSeeder extends Seeder
{
    public function run(): void
    {
        $aTypes = [
            ['name' => 'new_feature', 'label' => 'Nouvelle fonctionnalité', 'color' => 'success', 'icon' => 'bi-plus-circle', 'sort_order' => 1],
            ['name' => 'bug_fix', 'label' => 'Correction de bug', 'color' => 'danger', 'icon' => 'bi-bug', 'sort_order' => 2],
            ['name' => 'improvement', 'label' => 'Amélioration', 'color' => 'primary', 'icon' => 'bi-arrow-up-circle', 'sort_order' => 3],
            ['name' => 'maintenance', 'label' => 'Maintenance', 'color' => 'warning', 'icon' => 'bi-tools', 'sort_order' => 4],
            ['name' => 'support', 'label' => 'Support', 'color' => 'info', 'icon' => 'bi-headset', 'sort_order' => 5],
            ['name' => 'migration', 'label' => 'Migration / Mise à jour', 'color' => 'secondary', 'icon' => 'bi-arrow-repeat', 'sort_order' => 6],
        ];

        foreach ($aTypes as $aType) {
            RequestType::updateOrCreate(['name' => $aType['name']], $aType);
        }
    }
}
