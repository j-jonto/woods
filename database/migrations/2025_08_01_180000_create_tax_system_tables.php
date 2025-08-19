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
        // جدول أنواع الضرائب
        Schema::create('tax_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('rate', 5, 2); // نسبة الضريبة
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_compound')->default(false); // ضريبة مركبة
            $table->timestamps();
        });

        // جدول الضرائب على العناصر
        Schema::create('item_taxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('tax_type_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('tax_type_id')->references('id')->on('tax_types')->onDelete('cascade');
            $table->unique(['item_id', 'tax_type_id']);
        });

        // جدول الضرائب على المبيعات
        Schema::create('sales_taxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('tax_type_id');
            $table->decimal('taxable_amount', 15, 2);
            $table->decimal('tax_amount', 15, 2);
            $table->timestamps();

            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('cascade');
            $table->foreign('tax_type_id')->references('id')->on('tax_types')->onDelete('cascade');
        });

        // جدول الضرائب على المشتريات
        Schema::create('purchase_taxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('tax_type_id');
            $table->decimal('taxable_amount', 15, 2);
            $table->decimal('tax_amount', 15, 2);
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('tax_type_id')->references('id')->on('tax_types')->onDelete('cascade');
        });

        // إضافة حقول الضرائب لطلبات البيع
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->default(0)->after('total_amount');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('subtotal');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('tax_amount');
            $table->decimal('shipping_amount', 15, 2)->default(0)->after('discount_amount');
        });

        // إضافة حقول الضرائب لأوامر الشراء
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->default(0)->after('total_amount');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('subtotal');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('tax_amount');
            $table->decimal('shipping_amount', 15, 2)->default(0)->after('discount_amount');
        });

        // إضافة حقول الضرائب لعناصر طلبات البيع
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->decimal('tax_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('tax_amount');
        });

        // إضافة حقول الضرائب لعناصر أوامر الشراء
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('tax_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('tax_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['tax_amount', 'discount_amount']);
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn(['tax_amount', 'discount_amount']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_amount', 'discount_amount', 'shipping_amount']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_amount', 'discount_amount', 'shipping_amount']);
        });

        Schema::dropIfExists('purchase_taxes');
        Schema::dropIfExists('sales_taxes');
        Schema::dropIfExists('item_taxes');
        Schema::dropIfExists('tax_types');
    }
}; 