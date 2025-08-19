<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('opening_balance', 15, 2)->default(0)->after('phone');
            $table->decimal('current_balance', 15, 2)->default(0)->after('opening_balance');
            $table->decimal('total_payables', 15, 2)->default(0)->after('current_balance');
            $table->decimal('total_payments', 15, 2)->default(0)->after('total_payables');
            $table->date('last_transaction_date')->nullable()->after('total_payments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'opening_balance',
                'current_balance',
                'total_payables', 
                'total_payments',
                'last_transaction_date'
            ]);
        });
    }
}; 