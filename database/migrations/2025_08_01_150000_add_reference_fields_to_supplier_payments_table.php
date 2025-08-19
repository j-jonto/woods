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
        Schema::table('supplier_payments', function (Blueprint $table) {
            // إضافة حقول الربط مع المراجع
            $table->string('reference_type')->nullable()->after('method');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->enum('status', ['pending', 'paid'])->default('pending')->after('reference_id');
            
            // إضافة فهارس للبحث السريع
            $table->index(['reference_type', 'reference_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropIndex(['reference_type', 'reference_id']);
            $table->dropIndex(['status']);
            $table->dropColumn(['reference_type', 'reference_id', 'status']);
        });
    }
}; 