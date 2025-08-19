<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 50)->unique();
            $table->date('order_date');
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 15, 2);
            $table->enum('status', ['draft', 'released', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down()
    {
        Schema::dropIfExists('production_orders');
    }
}; 