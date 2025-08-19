<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->enum('type', ['raw_material', 'wip', 'finished_goods']);
            $table->string('unit_of_measure', 20);
            $table->decimal('standard_cost', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('reorder_point', 15, 2)->default(0);
            $table->decimal('reorder_quantity', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('item_categories')->nullOnDelete();
        });
    }
    public function down()
    {
        Schema::dropIfExists('items');
    }
}; 