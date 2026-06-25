<?php

namespace Database\Seeders;

use App\Models\TaskType;
use Illuminate\Database\Seeder;

class TaskTypeSeeder extends Seeder
{
    public function run(): void
    {
        $aTypes = [
            ['name' => 'analysis', 'label' => 'Analyse', 'color' => 'info', 'icon' => 'bi-search', 'sort_order' => 1],
            ['name' => 'development', 'label' => 'Développement', 'color' => 'primary', 'icon' => 'bi-code-slash', 'sort_order' => 2],
            ['name' => 'testing', 'label' => 'Tests', 'color' => 'warning', 'icon' => 'bi-bug', 'sort_order' => 3],
            ['name' => 'documentation', 'label' => 'Documentation', 'color' => 'secondary', 'icon' => 'bi-file-text', 'sort_order' => 4],
            ['name' => 'deployment', 'label' => 'Déploiement', 'color' => 'success', 'icon' => 'bi-cloud-upload', 'sort_order' => 5],
            ['name' => 'support', 'label' => 'Support', 'color' => 'danger', 'icon' => 'bi-headset', 'sort_order' => 6],
            ['name' => 'meeting', 'label' => 'Réunion', 'color' => 'info', 'icon' => 'bi-people', 'sort_order' => 7],
        ];

        foreach ($aTypes as $aType) {
            TaskType::updateOrCreate(['name' => $aType['name']], $aType);
        }
    }
}
