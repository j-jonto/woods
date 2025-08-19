# المرحلة السابعة: تحسينات الأداء

## الإصلاحات المطبقة

### 1. تحسين قواعد البيانات ✅

#### التغييرات:
- **ملف**: `database/migrations/2025_08_01_190000_optimize_database_performance.php`
- **إضافة**: فهارس وتحسينات لقواعد البيانات

#### الميزات الجديدة:
- **فهارس متقدمة**: فهارس لجميع الجداول الرئيسية
- **فهارس مركبة**: فهارس متعددة الأعمدة للاستعلامات المعقدة
- **جداول مؤقتة**: جدول للتخزين المؤقت للتقارير
- **إحصائيات الأداء**: جدول لتتبع قياسات الأداء
- **إحصائيات الاستخدام**: جدول لتتبع استخدام النظام

#### الكود المضاف:
```php
// فهارس للمبيعات
Schema::table('sales_orders', function (Blueprint $table) {
    $table->index(['status', 'order_date']);
    $table->index(['customer_id', 'order_date']);
    $table->index(['branch_id', 'order_date']);
    $table->index(['currency_id', 'order_date']);
});

// فهارس للمشتريات
Schema::table('purchase_orders', function (Blueprint $table) {
    $table->index(['status', 'order_date']);
    $table->index(['supplier_id', 'order_date']);
    $table->index(['branch_id', 'order_date']);
    $table->index(['currency_id', 'order_date']);
});

// جدول التخزين المؤقت للتقارير
Schema::create('report_cache', function (Blueprint $table) {
    $table->id();
    $table->string('report_key')->unique();
    $table->text('report_data');
    $table->timestamp('cached_at');
    $table->timestamp('expires_at');
    $table->timestamps();
    
    $table->index(['report_key', 'expires_at']);
});

// جدول إحصائيات الأداء
Schema::create('performance_metrics', function (Blueprint $table) {
    $table->id();
    $table->string('metric_name');
    $table->string('metric_type'); // query, page_load, api_call
    $table->decimal('execution_time', 10, 4); // بالثواني
    $table->integer('memory_usage')->nullable(); // بالبايت
    $table->text('query_sql')->nullable();
    $table->json('parameters')->nullable();
    $table->timestamp('executed_at');
    $table->timestamps();
    
    $table->index(['metric_name', 'executed_at']);
    $table->index(['metric_type', 'executed_at']);
    $table->index(['execution_time', 'executed_at']);
});
```

### 2. نظام التخزين المؤقت ✅

#### التغييرات:
- **ملف**: `app/Services/CacheService.php`
- **إضافة**: خدمة شاملة للتخزين المؤقت

#### الميزات:
- **تخزين مؤقت للتقارير**: تخزين مؤقت للتقارير المعقدة
- **تخزين مؤقت للإحصائيات**: تخزين مؤقت للإحصائيات
- **تخزين مؤقت للرسوم البيانية**: تخزين مؤقت لبيانات الرسوم البيانية
- **تخزين مؤقت للتنبيهات**: تخزين مؤقت للتنبيهات
- **تخزين مؤقت ذكي**: تخزين مؤقت ذكي للبيانات المتكررة
- **تنظيف تلقائي**: تنظيف تلقائي للبيانات المنتهية الصلاحية
- **إحصائيات التخزين المؤقت**: إحصائيات شاملة للتخزين المؤقت

#### الكود المضاف:
```php
class CacheService
{
    public static function cacheReport($key, $data, $minutes = 60)
    {
        $expiresAt = Carbon::now()->addMinutes($minutes);
        
        ReportCache::updateOrCreate(
            ['report_key' => $key],
            [
                'report_data' => json_encode($data),
                'cached_at' => Carbon::now(),
                'expires_at' => $expiresAt,
            ]
        );

        Cache::put($key, $data, $minutes * 60);
        return true;
    }

    public static function getCachedReport($key)
    {
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $report = ReportCache::where('report_key', $key)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($report) {
            $data = json_decode($report->report_data, true);
            Cache::put($key, $data, Carbon::now()->diffInSeconds($report->expires_at));
            return $data;
        }

        return null;
    }

    public static function smartCache($key, $callback, $options = [])
    {
        $defaultOptions = [
            'minutes' => 30,
            'tags' => [],
            'condition' => null,
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        if ($options['condition'] && !$options['condition']()) {
            return $callback();
        }
        
        $cached = self::getCachedReport($key);
        
        if ($cached === null) {
            $data = $callback();
            self::cacheReport($key, $data, $options['minutes']);
            return $data;
        }
        
        return $cached;
    }
}
```

### 3. نموذج ReportCache ✅

#### التغييرات:
- **ملف**: `app/Models/ReportCache.php`
- **إضافة**: نموذج لإدارة التخزين المؤقت للتقارير

#### الميزات:
- **إدارة التقارير**: إدارة التقارير المخزنة مؤقتاً
- **التحقق من الصلاحية**: التحقق من صلاحية التقارير
- **إحصائيات التخزين المؤقت**: إحصائيات شاملة
- **تنظيف تلقائي**: تنظيف التقارير المنتهية الصلاحية
- **تحسين قاعدة البيانات**: تحسين قاعدة البيانات

#### الكود المضاف:
```php
class ReportCache extends Model
{
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function isValid()
    {
        return !$this->isExpired();
    }

    public function getDataAttribute()
    {
        return json_decode($this->report_data, true);
    }

    public function setDataAttribute($value)
    {
        $this->report_data = json_encode($value);
    }

    public static function getStatistics()
    {
        $total = self::count();
        $valid = self::valid()->count();
        $expired = self::expired()->count();
        $totalSize = self::sum(\DB::raw('LENGTH(report_data)'));
        $averageSize = $total > 0 ? $totalSize / $total : 0;

        return [
            'total_reports' => $total,
            'valid_reports' => $valid,
            'expired_reports' => $expired,
            'total_size_bytes' => $totalSize,
            'average_size_bytes' => round($averageSize, 2),
            'hit_rate_percentage' => $total > 0 ? round(($valid / $total) * 100, 2) : 0,
        ];
    }
}
```

### 4. نظام مراقبة الأداء ✅

#### التغييرات:
- **ملف**: `app/Services/PerformanceMonitorService.php`
- **إضافة**: خدمة شاملة لمراقبة الأداء

#### الميزات:
- **مراقبة الاستعلامات**: مراقبة أداء استعلامات قاعدة البيانات
- **مراقبة تحميل الصفحات**: مراقبة وقت تحميل الصفحات
- **مراقبة API**: مراقبة أداء استدعاءات API
- **تسجيل القياسات**: تسجيل قياسات الأداء
- **تحليل الأداء**: تحليل شامل للأداء
- **إحصائيات الاستخدام**: إحصائيات استخدام النظام
- **تقارير الأداء**: تقارير شاملة للأداء

#### الكود المضاف:
```php
class PerformanceMonitorService
{
    public static function monitorQuery($callback, $name = null)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            $result = $callback();
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage();
            
            $executionTime = $endTime - $startTime;
            $memoryUsage = $endMemory - $startMemory;

            self::recordMetric(
                $name ?: 'database_query',
                'query',
                $executionTime,
                $memoryUsage
            );

            return $result;
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            self::recordMetric(
                $name ?: 'database_query_error',
                'query_error',
                $executionTime,
                null,
                null,
                ['error' => $e->getMessage()]
            );
            
            throw $e;
        }
    }

    public static function analyzePerformance($days = 7)
    {
        $performanceStats = self::getPerformanceStatistics($days);
        $usageStats = self::getUsageStatistics($days);
        
        $analysis = [
            'performance_issues' => [],
            'recommendations' => [],
            'summary' => [],
        ];

        if ($performanceStats['average_execution_time'] > 1.0) {
            $analysis['performance_issues'][] = 'متوسط وقت التنفيذ بطيء (> 1 ثانية)';
            $analysis['recommendations'][] = 'تحسين الاستعلامات وإضافة فهارس';
        }

        return $analysis;
    }
}
```

### 5. نماذج مراقبة الأداء ✅

#### التغييرات:
- **ملف**: `app/Models/PerformanceMetrics.php`
- **ملف**: `app/Models/UsageStatistics.php`
- **إضافة**: نماذج لمراقبة الأداء والاستخدام

#### الميزات:
- **قياسات الأداء**: تتبع قياسات الأداء المختلفة
- **إحصائيات الاستخدام**: تتبع استخدام النظام
- **تحليل الأداء**: تحليل شامل للأداء
- **تنظيف البيانات**: تنظيف البيانات القديمة
- **إحصائيات متقدمة**: إحصائيات متقدمة للأداء

#### الكود المضاف:
```php
class PerformanceMetrics extends Model
{
    public static function getStatistics($days = 7)
    {
        $startDate = now()->subDays($days);
        
        $metrics = self::where('executed_at', '>=', $startDate)->get();
        
        return [
            'total_metrics' => $metrics->count(),
            'average_execution_time' => $metrics->avg('execution_time'),
            'max_execution_time' => $metrics->max('execution_time'),
            'min_execution_time' => $metrics->min('execution_time'),
            'total_memory_usage' => $metrics->sum('memory_usage'),
            'average_memory_usage' => $metrics->avg('memory_usage'),
        ];
    }
}

class UsageStatistics extends Model
{
    public static function getStatistics($days = 7)
    {
        $startDate = now()->subDays($days);
        
        $usage = self::where('accessed_at', '>=', $startDate)->get();
        
        return [
            'total_visits' => $usage->count(),
            'unique_users' => $usage->pluck('user_id')->unique()->count(),
            'average_load_time' => $usage->avg('load_time'),
            'max_load_time' => $usage->max('load_time'),
            'min_load_time' => $usage->min('load_time'),
        ];
    }
}
```

### 6. Command لتحسين الأداء ✅

#### التغييرات:
- **ملف**: `app/Console/Commands/OptimizePerformance.php`
- **إضافة**: command شامل لتحسين الأداء

#### الميزات:
- **تنظيف التخزين المؤقت**: تنظيف التخزين المؤقت
- **تنظيف البيانات القديمة**: تنظيف البيانات القديمة
- **تحليل الأداء**: تحليل شامل للأداء
- **تحسين قاعدة البيانات**: تحسين قاعدة البيانات
- **إحصائيات النظام**: إحصائيات شاملة للنظام

#### الكود المضاف:
```php
class OptimizePerformance extends Command
{
    protected $signature = 'performance:optimize {--clean-cache : تنظيف التخزين المؤقت} {--clean-data : تنظيف البيانات القديمة} {--analyze : تحليل الأداء} {--days=30 : عدد الأيام للتنظيف}';

    public function handle()
    {
        $this->info('بدء تحسين الأداء...');

        $days = $this->option('days');
        $cleanCache = $this->option('clean-cache');
        $cleanData = $this->option('clean-data');
        $analyze = $this->option('analyze');

        try {
            if ($cleanCache) {
                $this->cleanCache();
            }

            if ($cleanData) {
                $this->cleanOldData($days);
            }

            if ($analyze) {
                $this->analyzePerformance();
            }

            $this->optimizeDatabase();

            $this->info('تم إكمال تحسين الأداء بنجاح!');
            return 0;

        } catch (\Exception $e) {
            $this->error('خطأ في تحسين الأداء: ' . $e->getMessage());
            return 1;
        }
    }
}
```

## النتائج المتوقعة

### قبل الإصلاح:
- ❌ استعلامات بطيئة بدون فهارس
- ❌ لا يوجد تخزين مؤقت
- ❌ لا يوجد مراقبة للأداء
- ❌ لا يوجد تحليل للأداء
- ❌ لا يوجد تنظيف تلقائي للبيانات

### بعد الإصلاح:
- ✅ استعلامات سريعة مع فهارس متقدمة
- ✅ نظام تخزين مؤقت شامل
- ✅ مراقبة شاملة للأداء
- ✅ تحليل متقدم للأداء
- ✅ تنظيف تلقائي للبيانات
- ✅ تحسين قاعدة البيانات
- ✅ إحصائيات متقدمة
- ✅ تقارير الأداء

## الاختبار المطلوب

### 1. اختبار الفهارس:
1. تشغيل migration الفهارس
2. اختبار سرعة الاستعلامات
3. مقارنة الأداء قبل وبعد
4. التحقق من استخدام الفهارس

### 2. اختبار التخزين المؤقت:
1. اختبار تخزين التقارير
2. اختبار استرجاع البيانات
3. اختبار انتهاء الصلاحية
4. اختبار التنظيف التلقائي

### 3. اختبار مراقبة الأداء:
1. اختبار تسجيل القياسات
2. اختبار مراقبة الاستعلامات
3. اختبار مراقبة الصفحات
4. اختبار تحليل الأداء

### 4. اختبار Commands:
1. `php artisan performance:optimize --analyze`
2. `php artisan performance:optimize --clean-cache`
3. `php artisan performance:optimize --clean-data`
4. `php artisan performance:optimize --clean-cache --clean-data --analyze`

### 5. اختبار الأداء:
1. قياس سرعة التطبيق
2. قياس استخدام الذاكرة
3. قياس سرعة قاعدة البيانات
4. مقارنة الأداء الإجمالي

## الخطوات التالية

### المرحلة الثامنة: تحسينات الأمان
1. تشفير البيانات الحساسة
2. حماية من الهجمات
3. مراجعة الأمان
4. تحسين الصلاحيات

## ملاحظات مهمة

1. **النسخ الاحتياطي**: تم الاحتفاظ بنسخ احتياطية قبل التطبيق
2. **التسجيل**: جميع الأخطاء مسجلة في ملفات السجل
3. **الاختبار**: يجب اختبار جميع الوظائف قبل الانتقال للمرحلة التالية
4. **التوثيق**: جميع التغييرات موثقة في هذا الملف
5. **الفهارس**: تم إضافة فهارس متقدمة لجميع الجداول
6. **التخزين المؤقت**: تم إضافة نظام تخزين مؤقت شامل
7. **مراقبة الأداء**: تم إضافة مراقبة شاملة للأداء
8. **تحليل الأداء**: تم إضافة تحليل متقدم للأداء
9. **التحسين**: تم تحسين قاعدة البيانات والأداء
10. **التكامل**: جميع الأنظمة متكاملة مع بعضها البعض

## أوامر التشغيل

### تحسين الأداء:
```bash
# تحليل الأداء
php artisan performance:optimize --analyze

# تنظيف التخزين المؤقت
php artisan performance:optimize --clean-cache

# تنظيف البيانات القديمة
php artisan performance:optimize --clean-data

# تنظيف شامل مع التحليل
php artisan performance:optimize --clean-cache --clean-data --analyze

# تنظيف البيانات الأقدم من 60 يوم
php artisan performance:optimize --clean-data --days=60
```

### إضافة إلى Cron Job (اختياري):
```bash
# تشغيل تحسين الأداء يومياً
0 2 * * * cd /path/to/project && php artisan performance:optimize --clean-cache --clean-data

# تشغيل تحليل الأداء أسبوعياً
0 3 * * 0 cd /path/to/project && php artisan performance:optimize --analyze
```

## مؤشرات الأداء

### مؤشرات الأداء الجيدة:
- **وقت تنفيذ الاستعلامات**: < 0.1 ثانية
- **وقت تحميل الصفحات**: < 1 ثانية
- **استخدام الذاكرة**: < 50MB
- **معدل نجاح التخزين المؤقت**: > 80%

### مؤشرات الأداء التي تحتاج تحسين:
- **وقت تنفيذ الاستعلامات**: > 1 ثانية
- **وقت تحميل الصفحات**: > 3 ثواني
- **استخدام الذاكرة**: > 100MB
- **معدل نجاح التخزين المؤقت**: < 50% 