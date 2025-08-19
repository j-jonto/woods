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
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->string('entry_type')->default('manual')->after('description'); // manual, auto, adjustment
            $table->string('reference_type')->nullable()->after('entry_type'); // sales_order, purchase_order, etc.
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->boolean('is_posted')->default(false)->after('reference_id');
            $table->timestamp('posted_at')->nullable()->after('is_posted');
            $table->unsignedBigInteger('posted_by')->nullable()->after('posted_at');
            $table->foreign('posted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropForeign(['posted_by']);
            $table->dropColumn([
                'entry_type',
                'reference_type',
                'reference_id',
                'is_posted',
                'posted_at',
                'posted_by'
            ]);
        });
    }
}; 