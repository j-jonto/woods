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
        // جدول الفروع
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('manager_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_main_branch')->default(false);
            $table->timestamps();
        });

        // جدول مستخدمي الفروع
        Schema::create('branch_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_manager')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['branch_id', 'user_id']);
        });

        // جدول خزائن الفروع
        Schema::create('branch_treasuries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            $table->unique('branch_id');
        });

        // إضافة حقول الفرع للجداول الرئيسية
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('base_shipping_amount');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('base_shipping_amount');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('base_selling_price');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('is_active');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('last_transaction_date');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('last_transaction_date');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('default_branch_id')->nullable()->after('email');
        });

        // إضافة المفاتيح الأجنبية
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('default_branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['default_branch_id']);
            $table->dropColumn('default_branch_id');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::dropIfExists('branch_treasuries');
        Schema::dropIfExists('branch_users');
        Schema::dropIfExists('branches');
    }
}; 