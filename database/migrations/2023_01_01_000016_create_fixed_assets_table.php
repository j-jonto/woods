<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 15, 2);
            $table->integer('useful_life');
            $table->enum('depreciation_method', ['straight_line', 'reducing_balance']);
            $table->enum('status', ['active', 'disposed', 'sold'])->default('active');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('fixed_assets');
    }
}; 