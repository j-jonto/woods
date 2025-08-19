<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('production_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_order_id');
            $table->unsignedBigInteger('material_id'); // المواد الخام المستخدمة
            $table->decimal('planned_quantity', 15, 2); // الكمية المخططة
            $table->decimal('actual_quantity', 15, 2)->nullable(); // الكمية الفعلية المستخدمة
            $table->decimal('unit_cost', 15, 2)->default(0); // تكلفة الوحدة
            $table->decimal('total_cost', 15, 2)->default(0); // التكلفة الإجمالية
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('production_order_id')->references('id')->on('production_orders')->onDelete('cascade');
            $table->foreign('material_id')->references('id')->on('items');
        });
    }

    public function down()
    {
        Schema::dropIfExists('production_materials');
    }
}; 