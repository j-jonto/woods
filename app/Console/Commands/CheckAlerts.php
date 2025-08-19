<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class CheckAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:check {--resolve : حل التنبيهات المحلولة}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'فحص جميع التنبيهات في النظام';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('بدء فحص التنبيهات...');

        try {
            // فحص جميع التنبيهات
            NotificationService::checkAllAlerts();
            $this->info('✓ تم فحص جميع التنبيهات بنجاح');

            // حل التنبيهات المحلولة إذا تم طلب ذلك
            if ($this->option('resolve')) {
                NotificationService::resolveAlerts();
                $this->info('✓ تم حل التنبيهات المحلولة');
            }

            // عرض إحصائيات التنبيهات
            $stats = NotificationService::getAlertStatistics();
            $this->table(
                ['النوع', 'العدد'],
                [
                    ['إجمالي التنبيهات', $stats['total_alerts']],
                    ['تنبيهات غير محلولة', $stats['unresolved_alerts']],
                    ['تنبيهات حرجة', $stats['critical_alerts']],
                    ['تنبيهات عالية', $stats['high_alerts']],
                    ['تنبيهات متوسطة', $stats['medium_alerts']],
                    ['تنبيهات منخفضة', $stats['low_alerts']],
                ]
            );

            $this->info('تم إكمال فحص التنبيهات بنجاح!');
            return 0;

        } catch (\Exception $e) {
            $this->error('خطأ في فحص التنبيهات: ' . $e->getMessage());
            return 1;
        }
    }
} 