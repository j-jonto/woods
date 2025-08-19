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
        Schema::create('treasury', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('الخزنة العامة');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->decimal('total_receipts', 15, 2)->default(0);
            $table->decimal('total_payments', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('treasury_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('treasury_id')->default(1);
            $table->enum('type', ['receipt', 'payment', 'transfer', 'adjustment']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('reference_type')->nullable(); // expense, revenue, supplier_payment, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description');
            $table->date('transaction_date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('treasury_id')->references('id')->on('treasury');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treasury_transactions');
        Schema::dropIfExists('treasury');
    }
};
