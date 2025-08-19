<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('production_materials', function (Blueprint $table) {
            $table->decimal('used_quantity', 15, 2)->nullable()->after('actual_quantity');
            $table->decimal('actual_cost', 15, 2)->default(0)->after('total_cost');
        });
    }

    public function down()
    {
        Schema::table('production_materials', function (Blueprint $table) {
            $table->dropColumn(['used_quantity', 'actual_cost']);
        });
    }
}; 