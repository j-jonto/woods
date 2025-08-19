<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\ClearAllDataSeeder;
use Database\Seeders\ResetSystemSeeder;

class ResetSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:reset {--confirm : تأكيد العملية}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'حذف جميع البيانات وإعادة إنشاء البيانات الأساسية';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 بدء عملية إعادة تعيين النظام...');
        
        if (!$this->option('confirm')) {
            if (!$this->confirm('⚠️  تحذير: هذا الأمر سيحذف جميع البيانات! هل أنت متأكد؟')) {
                $this->error('تم إلغاء العملية.');
                return 1;
            }
        }

        $this->warn('🚨 سيتم حذف جميع البيانات من النظام!');
        
        if (!$this->confirm('هل تريد المتابعة؟')) {
            $this->error('تم إلغاء العملية.');
            return 1;
        }

        try {
            // الخطوة 1: حذف جميع البيانات
            $this->info('📤 حذف جميع البيانات...');
            $clearSeeder = new ClearAllDataSeeder();
            $clearSeeder->run();
            
            // الخطوة 2: إعادة إنشاء البيانات الأساسية
            $this->info('📥 إعادة إنشاء البيانات الأساسية...');
            $resetSeeder = new ResetSystemSeeder();
            $resetSeeder->run();
            
            $this->info('✅ تم إعادة تعيين النظام بنجاح!');
            $this->info('🎯 يمكنك الآن البدء في إدخال البيانات الجديدة.');
            
            $this->table(
                ['الخطوة التالية', 'الوصف'],
                [
                    ['إضافة الموردين', 'أضف موردين للاختبار'],
                    ['إضافة العملاء', 'أضف عملاء للاختبار'],
                    ['إضافة الأصناف', 'أضف مواد خام ومنتجات'],
                    ['إنشاء أوامر شراء', 'اختبر الربط التلقائي'],
                    ['إنشاء أوامر بيع', 'اختبر دورة العمل الكاملة'],
                ]
            );
            
        } catch (\Exception $e) {
            $this->error('❌ حدث خطأ أثناء إعادة تعيين النظام: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
} 