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
        // جدول العملات
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique(); // USD, EUR, SAR, etc.
            $table->string('symbol', 5); // $, €, ر.س, etc.
            $table->decimal('exchange_rate', 15, 6)->default(1.000000); // سعر الصرف مقابل العملة الأساسية
            $table->boolean('is_base_currency')->default(false); // العملة الأساسية
            $table->boolean('is_active')->default(true);
            $table->integer('decimal_places')->default(2); // عدد الخانات العشرية
            $table->timestamps();
        });

        // جدول أسعار الصرف التاريخية
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_currency_id');
            $table->unsignedBigInteger('to_currency_id');
            $table->decimal('rate', 15, 6);
            $table->date('rate_date');
            $table->string('source')->nullable(); // مصدر سعر الصرف
            $table->timestamps();

            $table->foreign('from_currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('to_currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->unique(['from_currency_id', 'to_currency_id', 'rate_date']);
        });

        // إضافة حقول العملة للجداول الرئيسية
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('shipping_amount');
            $table->decimal('exchange_rate', 15, 6)->default(1.000000)->after('currency_id');
            $table->decimal('base_total_amount', 15, 2)->default(0)->after('exchange_rate');
            $table->decimal('base_subtotal', 15, 2)->default(0)->after('base_total_amount');
            $table->decimal('base_tax_amount', 15, 2)->default(0)->after('base_subtotal');
            $table->decimal('base_discount_amount', 15, 2)->default(0)->after('base_tax_amount');
            $table->decimal('base_shipping_amount', 15, 2)->default(0)->after('base_discount_amount');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('shipping_amount');
            $table->decimal('exchange_rate', 15, 6)->default(1.000000)->after('currency_id');
            $table->decimal('base_total_amount', 15, 2)->default(0)->after('exchange_rate');
            $table->decimal('base_subtotal', 15, 2)->default(0)->after('base_total_amount');
            $table->decimal('base_tax_amount', 15, 2)->default(0)->after('base_subtotal');
            $table->decimal('base_discount_amount', 15, 2)->default(0)->after('base_tax_amount');
            $table->decimal('base_shipping_amount', 15, 2)->default(0)->after('base_discount_amount');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('standard_cost');
            $table->decimal('base_standard_cost', 15, 2)->default(0)->after('currency_id');
            $table->decimal('base_selling_price', 15, 2)->default(0)->after('base_standard_cost');
        });

        Schema::table('treasury', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('current_balance');
            $table->decimal('base_opening_balance', 15, 2)->default(0)->after('currency_id');
            $table->decimal('base_current_balance', 15, 2)->default(0)->after('base_opening_balance');
        });

        Schema::table('treasury_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('amount');
            $table->decimal('exchange_rate', 15, 6)->default(1.000000)->after('currency_id');
            $table->decimal('base_amount', 15, 2)->default(0)->after('exchange_rate');
        });

        // إضافة المفاتيح الأجنبية
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::table('treasury', function (Blueprint $table) {
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::table('treasury_transactions', function (Blueprint $table) {
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treasury_transactions', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['currency_id', 'exchange_rate', 'base_amount']);
        });

        Schema::table('treasury', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['currency_id', 'base_opening_balance', 'base_current_balance']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['currency_id', 'base_standard_cost', 'base_selling_price']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['currency_id', 'exchange_rate', 'base_total_amount', 'base_subtotal', 'base_tax_amount', 'base_discount_amount', 'base_shipping_amount']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['currency_id', 'exchange_rate', 'base_total_amount', 'base_subtotal', 'base_tax_amount', 'base_discount_amount', 'base_shipping_amount']);
        });

        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
    }
}; 