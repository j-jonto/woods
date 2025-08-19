<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use App\Models\ItemCategory;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::firstOrCreate(['code' => 'MAIN'], [
            'name' => 'Main Warehouse',
            'location' => 'Factory Site',
            'is_active' => true,
        ]);
        ItemCategory::firstOrCreate(['name' => 'Wood'], [
            'description' => 'Raw wood materials',
        ]);
        ItemCategory::firstOrCreate(['name' => 'WIP'], [
            'description' => 'Work in Progress',
        ]);
        ItemCategory::firstOrCreate(['name' => 'Finished Goods'], [
            'description' => 'Completed broom handles',
        ]);
    }
} 