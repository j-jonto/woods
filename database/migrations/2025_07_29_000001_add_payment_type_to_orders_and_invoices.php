<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->enum('payment_type', ['cash', 'credit'])->default('cash')->after('status');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('payment_type', ['cash', 'credit'])->default('cash')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }
}; 