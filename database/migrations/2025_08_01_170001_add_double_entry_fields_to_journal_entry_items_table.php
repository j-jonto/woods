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
        Schema::table('journal_entry_items', function (Blueprint $table) {
            $table->string('entry_type')->default('debit')->after('credit'); // debit, credit
            $table->string('reference_type')->nullable()->after('entry_type');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entry_items', function (Blueprint $table) {
            $table->dropColumn([
                'entry_type',
                'reference_type',
                'reference_id'
            ]);
        });
    }
}; 