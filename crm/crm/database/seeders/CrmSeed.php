<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CrmSeed extends Seeder
{
    public function run(): void
    {
        $now = now();

        if (DB::table('teams')->count() === 0) {
            DB::table('teams')->insert([
                ['name' => 'Sales', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Marketing', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Support', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (DB::table('categories')->count() === 0) {
            DB::table('categories')->insert([
                ['name' => 'Plan de Igualdad', 'code' => 'plan_igualdad', 'applies_to' => 'account', 'selection_type' => 'single', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'RSE', 'code' => 'rse', 'applies_to' => 'account', 'selection_type' => 'single', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Distintivo de Igualdad', 'code' => 'dist_igualdad', 'applies_to' => 'account', 'selection_type' => 'single', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Interés Territorial', 'code' => 'interes_territorial', 'applies_to' => 'account', 'selection_type' => 'single', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }
}