<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create base currency (Saudi Riyal)
        Currency::firstOrCreate(['code' => 'SAR'], [
            'name' => 'Saudi Riyal',
            'symbol' => 'ر.س',
            'exchange_rate' => 1.000000,
            'is_base_currency' => true,
            'is_active' => true,
            'decimal_places' => 2,
        ]);

        // Create US Dollar
        Currency::firstOrCreate(['code' => 'USD'], [
            'name' => 'US Dollar',
            'symbol' => '$',
            'exchange_rate' => 3.750000,
            'is_base_currency' => false,
            'is_active' => true,
            'decimal_places' => 2,
        ]);

        // Create Euro
        Currency::firstOrCreate(['code' => 'EUR'], [
            'name' => 'Euro',
            'symbol' => '€',
            'exchange_rate' => 4.100000,
            'is_base_currency' => false,
            'is_active' => true,
            'decimal_places' => 2,
        ]);

        // Create British Pound
        Currency::firstOrCreate(['code' => 'GBP'], [
            'name' => 'British Pound',
            'symbol' => '£',
            'exchange_rate' => 4.800000,
            'is_base_currency' => false,
            'is_active' => true,
            'decimal_places' => 2,
        ]);

        // Create UAE Dirham
        Currency::firstOrCreate(['code' => 'AED'], [
            'name' => 'UAE Dirham',
            'symbol' => 'د.إ',
            'exchange_rate' => 1.020000,
            'is_base_currency' => false,
            'is_active' => true,
            'decimal_places' => 2,
        ]);
    }
} 