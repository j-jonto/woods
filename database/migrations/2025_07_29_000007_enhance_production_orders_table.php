<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('production_orders', function (Blueprint $table) {
            // إضافة bill_of_material_id
            $table->unsignedBigInteger('bill_of_material_id')->nullable()->after('item_id');
            $table->foreign('bill_of_material_id')->references('id')->on('bill_of_materials')->nullOnDelete();
            
            // إضافة work_center_id
            $table->unsignedBigInteger('work_center_id')->nullable()->after('bill_of_material_id');
            $table->foreign('work_center_id')->references('id')->on('work_centers')->nullOnDelete();
            
            // إضافة حقول التكلفة
            $table->decimal('total_cost', 15, 2)->default(0)->after('quantity');
            $table->decimal('unit_cost', 15, 2)->default(0)->after('total_cost');
            
            // إضافة حقول إضافية
            $table->text('notes')->nullable()->after('end_date');
            $table->decimal('actual_quantity', 15, 2)->nullable()->after('quantity'); // الكمية الفعلية المنتجة
        });
    }

    public function down()
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropForeign(['bill_of_material_id']);
            $table->dropForeign(['work_center_id']);
            $table->dropColumn([
                'bill_of_material_id', 
                'work_center_id', 
                'total_cost', 
                'unit_cost', 
                'notes',
                'actual_quantity'
            ]);
        });
    }
}; 