<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('barcode', 100)->unique()->nullable()->after('code');
        });
    }
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
}; 