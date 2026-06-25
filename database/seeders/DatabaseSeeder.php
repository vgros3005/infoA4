<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            RequestTypeSeeder::class,
            PrioritySeeder::class,
            TaskTypeSeeder::class,
            StatusSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
