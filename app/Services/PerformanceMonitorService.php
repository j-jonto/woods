<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PerformanceMetrics;
use App\Models\UsageStatistics;
use Carbon\Carbon;

class PerformanceMonitorService
{
    /**
     * تسجيل قياس الأداء
     */
    public static function recordMetric($name, $type, $executionTime, $memoryUsage = null, $querySql = null, $parameters = null)
    {
        try {
            PerformanceMetrics::create([
                'metric_name' => $name,
                'metric_type' => $type,
                'execution_time' => $executionTime,
                'memory_usage' => $memoryUsage,
                'query_sql' => $querySql,
                'parameters' => $parameters ? json_encode($parameters) : null,
                'executed_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record performance metric', [
                'error' => $e->getMessage(),
                'metric_name' => $name,
                'type' => $type,
            ]);
        }
    }

    /**
     * تسجيل إحصائيات الاستخدام
     */
    public static function recordUsage($pageName, $actionName = null, $userId = null, $loadTime = null)
    {
        try {
            UsageStatistics::create([
                'page_name' => $pageName,
                'action_name' => $actionName,
                'user_id' => $userId,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'load_time' => $loadTime,
                'accessed_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record usage statistics', [
                'error' => $e->getMessage(),
                'page_name' => $pageName,
            ]);
        }
    }

    /**
     * مراقبة استعلام قاعدة البيانات
     */
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

    /**
     * مراقبة تحميل الصفحة
     */
    public static function monitorPageLoad($callback, $pageName)
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
                "page_load_{$pageName}",
                'page_load',
                $executionTime,
                $memoryUsage
            );

            self::recordUsage($pageName, null, auth()->id(), $executionTime * 1000);

            return $result;
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            self::recordMetric(
                "page_load_error_{$pageName}",
                'page_load_error',
                $executionTime,
                null,
                null,
                ['error' => $e->getMessage()]
            );
            
            throw $e;
        }
    }

    /**
     * مراقبة استدعاء API
     */
    public static function monitorApiCall($callback, $endpoint)
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
                "api_call_{$endpoint}",
                'api_call',
                $executionTime,
                $memoryUsage
            );

            return $result;
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            self::recordMetric(
                "api_call_error_{$endpoint}",
                'api_call_error',
                $executionTime,
                null,
                null,
                ['error' => $e->getMessage()]
            );
            
            throw $e;
        }
    }

    /**
     * الحصول على إحصائيات الأداء
     */
    public static function getPerformanceStatistics($days = 7)
    {
        $startDate = Carbon::now()->subDays($days);
        
        $metrics = PerformanceMetrics::where('executed_at', '>=', $startDate)->get();
        
        $statistics = [
            'total_metrics' => $metrics->count(),
            'average_execution_time' => $metrics->avg('execution_time'),
            'max_execution_time' => $metrics->max('execution_time'),
            'min_execution_time' => $metrics->min('execution_time'),
            'total_memory_usage' => $metrics->sum('memory_usage'),
            'average_memory_usage' => $metrics->avg('memory_usage'),
        ];

        // إحصائيات حسب النوع
        $typeStats = $metrics->groupBy('metric_type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'average_time' => $group->avg('execution_time'),
                'max_time' => $group->max('execution_time'),
                'average_memory' => $group->avg('memory_usage'),
            ];
        });

        $statistics['by_type'] = $typeStats;

        // أبطأ 10 عمليات
        $slowestOperations = PerformanceMetrics::where('executed_at', '>=', $startDate)
            ->orderBy('execution_time', 'desc')
            ->limit(10)
            ->get(['metric_name', 'metric_type', 'execution_time', 'executed_at']);

        $statistics['slowest_operations'] = $slowestOperations;

        return $statistics;
    }

    /**
     * الحصول على إحصائيات الاستخدام
     */
    public static function getUsageStatistics($days = 7)
    {
        $startDate = Carbon::now()->subDays($days);
        
        $usage = UsageStatistics::where('accessed_at', '>=', $startDate)->get();
        
        $statistics = [
            'total_visits' => $usage->count(),
            'unique_users' => $usage->pluck('user_id')->unique()->count(),
            'average_load_time' => $usage->avg('load_time'),
            'max_load_time' => $usage->max('load_time'),
            'min_load_time' => $usage->min('load_time'),
        ];

        // أكثر الصفحات زيارة
        $mostVisitedPages = $usage->groupBy('page_name')
            ->map(function ($group) {
                return [
                    'visits' => $group->count(),
                    'average_load_time' => $group->avg('load_time'),
                    'unique_users' => $group->pluck('user_id')->unique()->count(),
                ];
            })
            ->sortByDesc('visits')
            ->take(10);

        $statistics['most_visited_pages'] = $mostVisitedPages;

        // أكثر المستخدمين نشاطاً
        $mostActiveUsers = $usage->whereNotNull('user_id')
            ->groupBy('user_id')
            ->map(function ($group) {
                return [
                    'visits' => $group->count(),
                    'average_load_time' => $group->avg('load_time'),
                    'last_visit' => $group->max('accessed_at'),
                ];
            })
            ->sortByDesc('visits')
            ->take(10);

        $statistics['most_active_users'] = $mostActiveUsers;

        return $statistics;
    }

    /**
     * تحليل الأداء
     */
    public static function analyzePerformance($days = 7)
    {
        $performanceStats = self::getPerformanceStatistics($days);
        $usageStats = self::getUsageStatistics($days);
        
        $analysis = [
            'performance_issues' => [],
            'recommendations' => [],
            'summary' => [],
        ];

        // تحليل الأداء البطيء
        if ($performanceStats['average_execution_time'] > 1.0) {
            $analysis['performance_issues'][] = 'متوسط وقت التنفيذ بطيء (> 1 ثانية)';
            $analysis['recommendations'][] = 'تحسين الاستعلامات وإضافة فهارس';
        }

        if ($performanceStats['max_execution_time'] > 5.0) {
            $analysis['performance_issues'][] = 'هناك عمليات بطيئة جداً (> 5 ثواني)';
            $analysis['recommendations'][] = 'مراجعة العمليات البطيئة وتحسينها';
        }

        // تحليل استخدام الذاكرة
        if ($performanceStats['average_memory_usage'] > 50 * 1024 * 1024) { // 50MB
            $analysis['performance_issues'][] = 'استخدام الذاكرة مرتفع';
            $analysis['recommendations'][] = 'تحسين استخدام الذاكرة وتنظيف البيانات المؤقتة';
        }

        // تحليل تحميل الصفحات
        if ($usageStats['average_load_time'] > 2000) { // 2 ثانية
            $analysis['performance_issues'][] = 'متوسط وقت تحميل الصفحات بطيء (> 2 ثانية)';
            $analysis['recommendations'][] = 'تحسين تحميل الصفحات واستخدام التخزين المؤقت';
        }

        // ملخص الأداء
        $analysis['summary'] = [
            'overall_performance' => $performanceStats['average_execution_time'] < 0.5 ? 'ممتاز' : 
                                   ($performanceStats['average_execution_time'] < 1.0 ? 'جيد' : 'يحتاج تحسين'),
            'memory_usage' => $performanceStats['average_memory_usage'] < 25 * 1024 * 1024 ? 'ممتاز' : 
                             ($performanceStats['average_memory_usage'] < 50 * 1024 * 1024 ? 'جيد' : 'يحتاج تحسين'),
            'page_load_speed' => $usageStats['average_load_time'] < 1000 ? 'ممتاز' : 
                                ($usageStats['average_load_time'] < 2000 ? 'جيد' : 'يحتاج تحسين'),
        ];

        return $analysis;
    }

    /**
     * تنظيف البيانات القديمة
     */
    public static function cleanOldData($days = 30)
    {
        $cutoffDate = Carbon::now()->subDays($days);
        
        $deletedMetrics = PerformanceMetrics::where('executed_at', '<', $cutoffDate)->delete();
        $deletedUsage = UsageStatistics::where('accessed_at', '<', $cutoffDate)->delete();
        
        return [
            'deleted_metrics' => $deletedMetrics,
            'deleted_usage_statistics' => $deletedUsage,
            'cutoff_date' => $cutoffDate,
        ];
    }

    /**
     * إنشاء تقرير الأداء
     */
    public static function generatePerformanceReport($days = 7)
    {
        $performanceStats = self::getPerformanceStatistics($days);
        $usageStats = self::getUsageStatistics($days);
        $analysis = self::analyzePerformance($days);
        
        return [
            'report_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'period_days' => $days,
            'performance_statistics' => $performanceStats,
            'usage_statistics' => $usageStats,
            'analysis' => $analysis,
        ];
    }
} 