<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    public function run(): void
    {
        $aPriorities = [
            ['name' => 'critical', 'label' => 'Critique', 'color' => 'danger', 'icon' => 'bi-exclamation-triangle-fill', 'requires_justification' => true, 'level' => 4, 'sort_order' => 1],
            ['name' => 'high', 'label' => 'Haute', 'color' => 'warning', 'icon' => 'bi-arrow-up-circle-fill', 'requires_justification' => true, 'level' => 3, 'sort_order' => 2],
            ['name' => 'medium', 'label' => 'Moyenne', 'color' => 'primary', 'icon' => 'bi-dash-circle-fill', 'requires_justification' => false, 'level' => 2, 'sort_order' => 3],
            ['name' => 'low', 'label' => 'Basse', 'color' => 'success', 'icon' => 'bi-arrow-down-circle-fill', 'requires_justification' => false, 'level' => 1, 'sort_order' => 4],
        ];

        foreach ($aPriorities as $aPriority) {
            Priority::updateOrCreate(['name' => $aPriority['name']], $aPriority);
        }
    }
}
