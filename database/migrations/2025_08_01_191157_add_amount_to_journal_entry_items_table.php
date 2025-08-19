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
            if (!Schema::hasColumn('journal_entry_items', 'amount')) {
                $table->decimal('amount', 15, 2)->default(0)->after('credit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entry_items', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entry_items', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }
};
