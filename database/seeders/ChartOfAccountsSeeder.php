<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asset Accounts
ChartOfAccount::create(['name' => 'Cash', 'type' => 'asset', 'code' => '10100']);
ChartOfAccount::create(['name' => 'Accounts Receivable', 'type' => 'asset', 'code' => '10200']);
ChartOfAccount::create(['name' => 'Inventory - Raw Materials', 'type' => 'asset', 'code' => '10300']);
ChartOfAccount::create(['name' => 'Inventory - Work in Progress', 'type' => 'asset', 'code' => '10400']);
ChartOfAccount::create(['name' => 'Inventory - Finished Goods', 'type' => 'asset', 'code' => '10500']);
ChartOfAccount::create(['name' => 'Prepaid Expenses', 'type' => 'asset', 'code' => '10600']);
ChartOfAccount::create(['name' => 'Fixed Assets - Land', 'type' => 'asset', 'code' => '15000']);
ChartOfAccount::create(['name' => 'Fixed Assets - Buildings', 'type' => 'asset', 'code' => '15100']);
ChartOfAccount::create(['name' => 'Fixed Assets - Machinery', 'type' => 'asset', 'code' => '15200']);
ChartOfAccount::create(['name' => 'Accumulated Depreciation', 'type' => 'asset', 'code' => '15900']);

        // Liability Accounts
        ChartOfAccount::create(['name' => 'Accounts Payable', 'type' => 'liability', 'code' => '20100']);
        ChartOfAccount::create(['name' => 'Wages Payable', 'type' => 'liability', 'code' => '20200']);
        ChartOfAccount::create(
            [
                'name' => 'Taxes Payable',
                'type' => 'liability',
                'code' => '20300'
            ]
        );
        ChartOfAccount::create(['name' => 'Loans Payable', 'type' => 'liability', 'code' => '25000']);

        // Equity Accounts
        ChartOfAccount::create(['name' => "Owner's Equity", 'type' => 'equity', 'code' => '30100']);
        ChartOfAccount::create(['name' => 'Retained Earnings', 'type' => 'equity', 'code' => '30200']);

        // Revenue Accounts
        ChartOfAccount::create(['name' => 'Sales Revenue', 'type' => 'revenue', 'code' => '40100']);
        ChartOfAccount::create(['name' => 'Other Income', 'type' => 'revenue', 'code' => '40200']);

        // Expense Accounts
        ChartOfAccount::create(['name' => 'Cost of Goods Sold', 'type' => 'expense', 'code' => '50100']);
        ChartOfAccount::create(['name' => 'Salaries Expense', 'type' => 'expense', 'code' => '50200']);
        ChartOfAccount::create(['name' => 'Rent Expense', 'type' => 'expense', 'code' => '50300']);
        ChartOfAccount::create(['name' => 'Utilities Expense', 'type' => 'expense', 'code' => '50400']);
        ChartOfAccount::create(['name' => 'Depreciation Expense', 'type' => 'expense', 'code' => '50500']);
        ChartOfAccount::create(['name' => 'Office Supplies Expense', 'type' => 'expense', 'code' => '50600']);
        ChartOfAccount::create(['name' => 'Maintenance Expense', 'type' => 'expense', 'code' => '50700']);
        ChartOfAccount::create(['name' => 'Interest Expense', 'type' => 'expense', 'code' => '50800']);
        ChartOfAccount::create(['name' => 'Research and Development Expense', 'type' => 'expense', 'code' => '50900']);
    }
} 