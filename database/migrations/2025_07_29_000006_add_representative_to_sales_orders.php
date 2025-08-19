<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('representative_id')->nullable()->after('customer_id');
            $table->foreign('representative_id')->references('id')->on('sales_representatives')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['representative_id']);
            $table->dropColumn('representative_id');
        });
    }
}; 