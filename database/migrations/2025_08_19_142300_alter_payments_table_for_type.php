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
        Schema::table('payments', function (Blueprint $table) {
            // Change the 'type' column from ENUM to a more flexible STRING
            // This allows for more transaction types like 'invoice'
            $table->string('type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Reverting back to ENUM is tricky as data might be lost.
            // For this rollback, we'll assume only the original values are wanted.
            // A better approach in a real project might be to check existing data.
            $table->enum('type', ['receipt', 'disbursement'])->change();
        });
    }
};
