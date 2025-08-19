<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageStatistics extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_name',
        'action_name',
        'user_id',
        'ip_address',
        'user_agent',
        'load_time',
        'accessed_at',
    ];

    protected $casts = [
        'load_time' => 'integer',
        'accessed_at' => 'datetime',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope للزيارات حسب الفترة الزمنية
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('accessed_at', [$startDate, $endDate]);
    }

    /**
     * Scope للزيارات حسب المستخدم
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope للزيارات حسب الصفحة
     */
    public function scopeByPage($query, $pageName)
    {
        return $query->where('page_name', $pageName);
    }

    /**
     * Scope للزيارات البطيئة
     */
    public function scopeSlow($query, $threshold = 2000)
    {
        return $query->where('load_time', '>', $threshold);
    }

    /**
     * Scope للزيارات السريعة
     */
    public function scopeFast($query, $threshold = 500)
    {
        return $query->where('load_time', '<', $threshold);
    }

    /**
     * الحصول على إحصائيات الاستخدام
     */
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
            'most_visited_pages' => $usage->groupBy('page_name')
                ->map(function ($group) {
                    return [
                        'visits' => $group->count(),
                        'average_load_time' => $group->avg('load_time'),
                        'unique_users' => $group->pluck('user_id')->unique()->count(),
                    ];
                })
                ->sortByDesc('visits')
                ->take(10),
        ];
    }

    /**
     * الحصول على أكثر الصفحات زيارة
     */
    public static function getMostVisitedPages($limit = 10, $days = 7)
    {
        $startDate = now()->subDays($days);
        
        return self::where('accessed_at', '>=', $startDate)
            ->select('page_name', \DB::raw('COUNT(*) as visits'), \DB::raw('AVG(load_time) as avg_load_time'))
            ->groupBy('page_name')
            ->orderBy('visits', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * الحصول على أكثر المستخدمين نشاطاً
     */
    public static function getMostActiveUsers($limit = 10, $days = 7)
    {
        $startDate = now()->subDays($days);
        
        return self::where('accessed_at', '>=', $startDate)
            ->whereNotNull('user_id')
            ->select('user_id', \DB::raw('COUNT(*) as visits'), \DB::raw('AVG(load_time) as avg_load_time'))
            ->groupBy('user_id')
            ->orderBy('visits', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * الحصول على أبطأ الصفحات
     */
    public static function getSlowestPages($limit = 10, $days = 7)
    {
        $startDate = now()->subDays($days);
        
        return self::where('accessed_at', '>=', $startDate)
            ->select('page_name', \DB::raw('AVG(load_time) as avg_load_time'), \DB::raw('COUNT(*) as visits'))
            ->groupBy('page_name')
            ->orderBy('avg_load_time', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * الحصول على إحصائيات حسب الساعة
     */
    public static function getHourlyStatistics($days = 7)
    {
        $startDate = now()->subDays($days);
        
        return self::where('accessed_at', '>=', $startDate)
            ->select(\DB::raw('HOUR(accessed_at) as hour'), \DB::raw('COUNT(*) as visits'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    /**
     * الحصول على إحصائيات حسب اليوم
     */
    public static function getDailyStatistics($days = 7)
    {
        $startDate = now()->subDays($days);
        
        return self::where('accessed_at', '>=', $startDate)
            ->select(\DB::raw('DATE(accessed_at) as date'), \DB::raw('COUNT(*) as visits'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * تنظيف البيانات القديمة
     */
    public static function cleanOldData($days = 30)
    {
        $cutoffDate = now()->subDays($days);
        return self::where('accessed_at', '<', $cutoffDate)->delete();
    }
} 