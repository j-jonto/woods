<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('cash_accounts')) {
            Schema::create('cash_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('type', ['cash', 'bank'])->default('cash');
                $table->string('account_number')->nullable();
                $table->decimal('opening_balance', 15, 2)->default(0);
                $table->decimal('current_balance', 15, 2)->default(0);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_accounts');
    }
};

