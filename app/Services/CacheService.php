<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\ReportCache;
use Carbon\Carbon;

class CacheService
{
    /**
     * تخزين مؤقت للتقارير
     */
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

        // تخزين مؤقت في Laravel Cache أيضاً
        Cache::put($key, $data, $minutes * 60);
        
        return true;
    }

    /**
     * استرجاع تقرير من التخزين المؤقت
     */
    public static function getCachedReport($key)
    {
        // محاولة الاسترجاع من Laravel Cache أولاً
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached;
        }

        // محاولة الاسترجاع من قاعدة البيانات
        $report = ReportCache::where('report_key', $key)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($report) {
            $data = json_decode($report->report_data, true);
            // إعادة تخزين في Laravel Cache
            Cache::put($key, $data, Carbon::now()->diffInSeconds($report->expires_at));
            return $data;
        }

        return null;
    }

    /**
     * حذف تقرير من التخزين المؤقت
     */
    public static function forgetReport($key)
    {
        Cache::forget($key);
        ReportCache::where('report_key', $key)->delete();
        return true;
    }

    /**
     * تنظيف التقارير المنتهية الصلاحية
     */
    public static function cleanExpiredReports()
    {
        $expired = ReportCache::where('expires_at', '<', Carbon::now())->get();
        
        foreach ($expired as $report) {
            Cache::forget($report->report_key);
        }
        
        ReportCache::where('expires_at', '<', Carbon::now())->delete();
        
        return $expired->count();
    }

    /**
     * تخزين مؤقت للإحصائيات
     */
    public static function cacheStatistics($type, $data, $minutes = 30)
    {
        $key = "statistics_{$type}";
        return self::cacheReport($key, $data, $minutes);
    }

    /**
     * استرجاع إحصائيات من التخزين المؤقت
     */
    public static function getCachedStatistics($type)
    {
        $key = "statistics_{$type}";
        return self::getCachedReport($key);
    }

    /**
     * تخزين مؤقت للرسوم البيانية
     */
    public static function cacheChartData($type, $data, $minutes = 60)
    {
        $key = "chart_{$type}";
        return self::cacheReport($key, $data, $minutes);
    }

    /**
     * استرجاع بيانات الرسم البياني من التخزين المؤقت
     */
    public static function getCachedChartData($type)
    {
        $key = "chart_{$type}";
        return self::getCachedReport($key);
    }

    /**
     * تخزين مؤقت للتنبيهات
     */
    public static function cacheAlerts($data, $minutes = 5)
    {
        return self::cacheReport('system_alerts', $data, $minutes);
    }

    /**
     * استرجاع التنبيهات من التخزين المؤقت
     */
    public static function getCachedAlerts()
    {
        return self::getCachedReport('system_alerts');
    }

    /**
     * تخزين مؤقت للبيانات المتكررة
     */
    public static function cacheFrequentData($key, $callback, $minutes = 15)
    {
        $cached = self::getCachedReport($key);
        
        if ($cached === null) {
            $data = $callback();
            self::cacheReport($key, $data, $minutes);
            return $data;
        }
        
        return $cached;
    }

    /**
     * تخزين مؤقت للاستعلامات المعقدة
     */
    public static function cacheQuery($key, $callback, $minutes = 30)
    {
        return self::cacheFrequentData("query_{$key}", $callback, $minutes);
    }

    /**
     * تخزين مؤقت للصفحات
     */
    public static function cachePage($key, $callback, $minutes = 60)
    {
        return self::cacheFrequentData("page_{$key}", $callback, $minutes);
    }

    /**
     * إحصائيات التخزين المؤقت
     */
    public static function getCacheStatistics()
    {
        $totalReports = ReportCache::count();
        $expiredReports = ReportCache::where('expires_at', '<', Carbon::now())->count();
        $activeReports = $totalReports - $expiredReports;
        
        $totalSize = ReportCache::sum(DB::raw('LENGTH(report_data)'));
        $averageSize = $totalReports > 0 ? $totalSize / $totalReports : 0;
        
        return [
            'total_reports' => $totalReports,
            'active_reports' => $activeReports,
            'expired_reports' => $expiredReports,
            'total_size_bytes' => $totalSize,
            'average_size_bytes' => round($averageSize, 2),
            'cache_hit_rate' => self::calculateCacheHitRate(),
        ];
    }

    /**
     * حساب معدل نجاح التخزين المؤقت
     */
    private static function calculateCacheHitRate()
    {
        // هذا مثال بسيط - في التطبيق الحقيقي يمكن تتبع الطلبات
        $totalRequests = Cache::get('cache_total_requests', 0);
        $cacheHits = Cache::get('cache_hits', 0);
        
        if ($totalRequests > 0) {
            return round(($cacheHits / $totalRequests) * 100, 2);
        }
        
        return 0;
    }

    /**
     * تسجيل طلب التخزين المؤقت
     */
    public static function logCacheRequest($hit = false)
    {
        Cache::increment('cache_total_requests');
        
        if ($hit) {
            Cache::increment('cache_hits');
        }
    }

    /**
     * تنظيف جميع التخزين المؤقت
     */
    public static function clearAllCache()
    {
        Cache::flush();
        ReportCache::truncate();
        
        // إعادة تعيين إحصائيات التخزين المؤقت
        Cache::forget('cache_total_requests');
        Cache::forget('cache_hits');
        
        return true;
    }

    /**
     * تحسين التخزين المؤقت
     */
    public static function optimizeCache()
    {
        // تنظيف التقارير المنتهية الصلاحية
        $cleaned = self::cleanExpiredReports();
        
        // إعادة تنظيم قاعدة البيانات
        DB::statement('VACUUM');
        
        return [
            'cleaned_reports' => $cleaned,
            'optimization_completed' => true,
        ];
    }

    /**
     * إنشاء مفتاح تخزين مؤقت فريد
     */
    public static function generateCacheKey($prefix, $params = [])
    {
        $key = $prefix;
        
        if (!empty($params)) {
            $key .= '_' . md5(serialize($params));
        }
        
        return $key;
    }

    /**
     * تخزين مؤقت ذكي للبيانات
     */
    public static function smartCache($key, $callback, $options = [])
    {
        $defaultOptions = [
            'minutes' => 30,
            'tags' => [],
            'condition' => null,
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // التحقق من الشرط إذا كان موجوداً
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