<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceMetrics extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_name',
        'metric_type',
        'execution_time',
        'memory_usage',
        'query_sql',
        'parameters',
        'executed_at',
    ];

    protected $casts = [
        'execution_time' => 'decimal:4',
        'memory_usage' => 'integer',
        'parameters' => 'array',
        'executed_at' => 'datetime',
    ];

    /**
     * Scope للقياسات حسب النوع
     */
    public function scopeByType($query, $type)
    {
        return $query->where('metric_type', $type);
    }

    /**
     * Scope للقياسات حسب الفترة الزمنية
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('executed_at', [$startDate, $endDate]);
    }

    /**
     * Scope للقياسات البطيئة
     */
    public function scopeSlow($query, $threshold = 1.0)
    {
        return $query->where('execution_time', '>', $threshold);
    }

    /**
     * Scope للقياسات السريعة
     */
    public function scopeFast($query, $threshold = 0.1)
    {
        return $query->where('execution_time', '<', $threshold);
    }

    /**
     * الحصول على إحصائيات الأداء
     */
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
            'by_type' => $metrics->groupBy('metric_type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'average_time' => $group->avg('execution_time'),
                    'max_time' => $group->max('execution_time'),
                    'average_memory' => $group->avg('memory_usage'),
                ];
            }),
        ];
    }

    /**
     * الحصول على أبطأ العمليات
     */
    public static function getSlowestOperations($limit = 10, $days = 7)
    {
        $startDate = now()->subDays($days);
        
        return self::where('executed_at', '>=', $startDate)
            ->orderBy('execution_time', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * الحصول على أكثر العمليات تكراراً
     */
    public static function getMostFrequentOperations($limit = 10, $days = 7)
    {
        $startDate = now()->subDays($days);
        
        return self::where('executed_at', '>=', $startDate)
            ->select('metric_name', 'metric_type', \DB::raw('COUNT(*) as frequency'), \DB::raw('AVG(execution_time) as avg_time'))
            ->groupBy('metric_name', 'metric_type')
            ->orderBy('frequency', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * تنظيف البيانات القديمة
     */
    public static function cleanOldData($days = 30)
    {
        $cutoffDate = now()->subDays($days);
        return self::where('executed_at', '<', $cutoffDate)->delete();
    }
} 