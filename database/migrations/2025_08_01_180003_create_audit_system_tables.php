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
        // جدول سجلات التدقيق
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action'); // create, update, delete, login, logout, etc.
                $table->string('table_name')->nullable();
                $table->unsignedBigInteger('record_id')->nullable();
                $table->text('old_values')->nullable(); // JSON
                $table->text('new_values')->nullable(); // JSON
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->text('description')->nullable();
                $table->string('severity')->default('info'); // info, warning, error, critical
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                $table->index(['table_name', 'record_id']);
                $table->index(['action', 'created_at']);
                $table->index(['user_id', 'created_at']);
            });
        }

        // جدول إعدادات التدقيق
        Schema::create('audit_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // جدول تقارير التدقيق
        Schema::create('audit_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_name');
            $table->string('report_type'); // security, financial, operational
            $table->date('report_date');
            $table->text('summary')->nullable();
            $table->json('findings')->nullable();
            $table->json('recommendations')->nullable();
            $table->string('status')->default('draft'); // draft, final, archived
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });

        // جدول تنبيهات التدقيق
        Schema::create('audit_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_type'); // security, financial, operational
            $table->string('severity'); // low, medium, high, critical
            $table->string('title');
            $table->text('description');
            $table->json('alert_data')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['alert_type', 'severity']);
            $table->index(['is_resolved', 'created_at']);
        });

        // جدول مراجعة الوصول
        Schema::create('access_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('review_type'); // login, permission, role
            $table->string('status'); // approved, denied, pending
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });

        // إضافة حقول التدقيق للجداول الرئيسية
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('default_branch_id');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->boolean('is_locked')->default(false)->after('last_login_ip');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->text('lock_reason')->nullable()->after('locked_at');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by')->nullable()->after('branch_id');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by')->nullable()->after('branch_id');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
        });

        // إضافة المفاتيح الأجنبية
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'approval_notes']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'approval_notes']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'last_login_ip', 'is_locked', 'locked_at', 'lock_reason']);
        });

        Schema::dropIfExists('access_reviews');
        Schema::dropIfExists('audit_alerts');
        Schema::dropIfExists('audit_reports');
        Schema::dropIfExists('audit_settings');
        Schema::dropIfExists('audit_logs');
    }
}; 