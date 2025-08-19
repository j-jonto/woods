<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('representative_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('representative_id');
            $table->enum('type', ['goods_received', 'payment', 'commission']); // بضاعة مستلمة، دفعة، عمولة
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign('representative_id')->references('id')->on('sales_representatives')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('representative_transactions');
    }
}; 