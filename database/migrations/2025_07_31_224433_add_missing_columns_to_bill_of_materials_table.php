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
        Schema::table('bill_of_materials', function (Blueprint $table) {
            // إضافة عمود quantity إذا كان مفقوداً
            if (!Schema::hasColumn('bill_of_materials', 'quantity')) {
                $table->decimal('quantity', 15, 2)->default(1);
            }
            
            // إضافة عمود description إذا كان مفقوداً
            if (!Schema::hasColumn('bill_of_materials', 'description')) {
                $table->text('description')->nullable();
            }
            
            // إضافة عمود is_active إذا كان مفقوداً
            if (!Schema::hasColumn('bill_of_materials', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_of_materials', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'description', 'is_active']);
        });
    }
};
