<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added missing import for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite لا يدعم تغيير enum مباشرة، لذا سنستخدم string بدلاً من enum
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('status_new')->nullable()->after('status');
        });

        // نسخ البيانات
        DB::statement("UPDATE sales_orders SET status_new = status");

        // حذف العمود القديم
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // إعادة تسمية العمود الجديد
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // لا نحتاج لتراجع لأننا نستخدم string بدلاً من enum
    }
};
