<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\DemoDataSeeder;

class SeedDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:seed {--fresh : إعادة إنشاء قاعدة البيانات}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ملء النظام ببيانات افتراضية لاختبار الوظائف';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('بدء إنشاء البيانات الافتراضية...');

        if ($this->option('fresh')) {
            $this->info('إعادة إنشاء قاعدة البيانات...');
            $this->call('migrate:fresh');
        }

        // تشغيل الـ seeder
        $seeder = new DemoDataSeeder();
        $seeder->run();

        $this->info('تم إنشاء البيانات الافتراضية بنجاح!');
        $this->info('');
        $this->info('بيانات تسجيل الدخول:');
        $this->info('البريد الإلكتروني: admin@example.com');
        $this->info('كلمة المرور: 123456');
        $this->info('');
        $this->info('يمكنك الآن اختبار جميع وظائف النظام!');
    }
} 