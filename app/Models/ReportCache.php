<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCache extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_key',
        'report_data',
        'cached_at',
        'expires_at',
    ];

    protected $casts = [
        'cached_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * التحقق من انتهاء صلاحية التقرير
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * التحقق من صحة التقرير
     */
    public function isValid()
    {
        return !$this->isExpired();
    }

    /**
     * الحصول على البيانات كـ array
     */
    public function getDataAttribute()
    {
        return json_decode($this->report_data, true);
    }

    /**
     * تعيين البيانات كـ array
     */
    public function setDataAttribute($value)
    {
        $this->report_data = json_encode($value);
    }

    /**
     * الحصول على الوقت المتبقي حتى انتهاء الصلاحية
     */
    public function getTimeToExpiryAttribute()
    {
        return $this->expires_at->diffInSeconds(now());
    }

    /**
     * الحصول على حجم البيانات بالبايت
     */
    public function getDataSizeAttribute()
    {
        return strlen($this->report_data);
    }

    /**
     * Scope للتقارير الصالحة
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope للتقارير المنتهية الصلاحية
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope للتقارير حسب النوع
     */
    public function scopeByType($query, $type)
    {
        return $query->where('report_key', 'like', "{$type}%");
    }

    /**
     * Scope للتقارير حسب الفترة الزمنية
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('cached_at', [$startDate, $endDate]);
    }

    /**
     * الحصول على إحصائيات التخزين المؤقت
     */
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

    /**
     * تنظيف التقارير المنتهية الصلاحية
     */
    public static function cleanExpired()
    {
        $expiredCount = self::expired()->count();
        self::expired()->delete();
        return $expiredCount;
    }

    /**
     * تنظيف التقارير القديمة
     */
    public static function cleanOld($days = 7)
    {
        $oldCount = self::where('cached_at', '<', now()->subDays($days))->count();
        self::where('cached_at', '<', now()->subDays($days))->delete();
        return $oldCount;
    }

    /**
     * الحصول على أكبر التقارير
     */
    public static function getLargestReports($limit = 10)
    {
        return self::select('report_key', \DB::raw('LENGTH(report_data) as size'))
            ->orderBy('size', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * الحصول على أكثر التقارير استخداماً
     */
    public static function getMostUsedReports($limit = 10)
    {
        return self::select('report_key', \DB::raw('COUNT(*) as usage_count'))
            ->groupBy('report_key')
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * تحسين قاعدة البيانات
     */
    public static function optimize()
    {
        // تنظيف التقارير المنتهية الصلاحية
        $expiredCount = self::cleanExpired();
        
        // تنظيف التقارير القديمة
        $oldCount = self::cleanOld();
        
        // إعادة تنظيم قاعدة البيانات
        \DB::statement('VACUUM');
        
        return [
            'expired_cleaned' => $expiredCount,
            'old_cleaned' => $oldCount,
            'optimization_completed' => true,
        ];
    }
} 