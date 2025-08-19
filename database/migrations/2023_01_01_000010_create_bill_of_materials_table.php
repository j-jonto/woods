<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('finished_good_id');
            $table->unsignedBigInteger('raw_material_id');
            $table->decimal('quantity', 15, 2);
            $table->timestamps();
            $table->foreign('finished_good_id')->references('id')->on('items');
            $table->foreign('raw_material_id')->references('id')->on('items');
        });
    }
    public function down()
    {
        Schema::dropIfExists('bill_of_materials');
    }
}; 