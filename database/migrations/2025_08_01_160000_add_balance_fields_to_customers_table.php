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
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('opening_balance', 15, 2)->default(0)->after('credit_limit');
            $table->decimal('current_balance', 15, 2)->default(0)->after('opening_balance');
            $table->decimal('total_receivables', 15, 2)->default(0)->after('current_balance');
            $table->decimal('total_payments', 15, 2)->default(0)->after('total_receivables');
            $table->date('last_transaction_date')->nullable()->after('total_payments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'opening_balance',
                'current_balance', 
                'total_receivables',
                'total_payments',
                'last_transaction_date'
            ]);
        });
    }
}; 