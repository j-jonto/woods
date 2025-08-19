<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\AuditAlert;
use App\Models\AuditReport;
use App\Models\AccessReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an audit event
     */
    public static function log($action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null, $description = null, $severity = 'info')
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'description' => $description,
                'severity' => $severity,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log audit event', [
                'error' => $e->getMessage(),
                'action' => $action,
                'table_name' => $tableName,
            ]);
        }
    }

    /**
     * Log user login
     */
    public static function logLogin($user, $success = true)
    {
        $action = $success ? 'login_success' : 'login_failed';
        $severity = $success ? 'info' : 'warning';
        
        self::log($action, 'users', $user->id, null, null, 
            $success ? 'User logged in successfully' : 'Failed login attempt', $severity);

        if ($success) {
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => Request::ip(),
            ]);
        }
    }

    /**
     * Log user logout
     */
    public static function logLogout($user)
    {
        self::log('logout', 'users', $user->id, null, null, 'User logged out');
    }

    /**
     * Log data changes
     */
    public static function logDataChange($action, $model, $oldValues = null, $newValues = null)
    {
        $tableName = $model->getTable();
        $recordId = $model->id;
        
        $description = ucfirst($action) . ' record in ' . $tableName;
        
        self::log($action, $tableName, $recordId, $oldValues, $newValues, $description);
    }

    /**
     * Create audit alert
     */
    public static function createAlert($type, $severity, $title, $description, $alertData = null)
    {
        try {
            AuditAlert::create([
                'alert_type' => $type,
                'severity' => $severity,
                'title' => $title,
                'description' => $description,
                'alert_data' => $alertData ? json_encode($alertData) : null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create audit alert', [
                'error' => $e->getMessage(),
                'type' => $type,
                'title' => $title,
            ]);
        }
    }

    /**
     * Check for suspicious activities
     */
    public static function checkSuspiciousActivities()
    {
        // Check for multiple failed login attempts
        $recentFailedLogins = AuditLog::where('action', 'login_failed')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->get()
            ->groupBy('ip_address');

        foreach ($recentFailedLogins as $ipAddress => $logs) {
            if (count($logs) >= 5) {
                self::createAlert(
                    'security',
                    'high',
                    'Multiple Failed Login Attempts',
                    "Multiple failed login attempts detected from IP: {$ipAddress}",
                    ['ip_address' => $ipAddress, 'attempts' => count($logs)]
                );
            }
        }

        // Check for unusual financial transactions
        $largeTransactions = AuditLog::where('table_name', 'sales_orders')
            ->where('action', 'create')
            ->where('created_at', '>=', now()->subHour())
            ->get();

        foreach ($largeTransactions as $transaction) {
            $newValues = json_decode($transaction->new_values, true);
            if (isset($newValues['total_amount']) && $newValues['total_amount'] > 10000) {
                self::createAlert(
                    'financial',
                    'medium',
                    'Large Transaction Detected',
                    "Large transaction detected: {$newValues['total_amount']}",
                    $newValues
                );
            }
        }
    }

    /**
     * Generate audit report
     */
    public static function generateReport($reportType, $startDate, $endDate, $userId = null)
    {
        $query = AuditLog::whereBetween('created_at', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $logs = $query->get();

        $summary = [
            'total_events' => $logs->count(),
            'unique_users' => $logs->pluck('user_id')->unique()->count(),
            'actions_breakdown' => $logs->groupBy('action')->map->count(),
            'severity_breakdown' => $logs->groupBy('severity')->map->count(),
            'tables_affected' => $logs->pluck('table_name')->unique()->filter()->count(),
        ];

        $findings = [];
        $recommendations = [];

        // Analyze login patterns
        $loginLogs = $logs->where('action', 'like', 'login%');
        if ($loginLogs->count() > 0) {
            $failedLogins = $loginLogs->where('action', 'login_failed')->count();
            $successLogins = $loginLogs->where('action', 'login_success')->count();
            
            if ($failedLogins > $successLogins * 0.1) {
                $findings[] = 'High rate of failed login attempts detected';
                $recommendations[] = 'Review and strengthen password policies';
            }
        }

        // Analyze data changes
        $dataChanges = $logs->whereIn('action', ['create', 'update', 'delete']);
        if ($dataChanges->count() > 0) {
            $deletions = $dataChanges->where('action', 'delete')->count();
            if ($deletions > $dataChanges->count() * 0.2) {
                $findings[] = 'High rate of data deletions detected';
                $recommendations[] = 'Review deletion policies and implement soft deletes where appropriate';
            }
        }

        // Check for unusual activity times
        $nightTimeLogs = $logs->filter(function ($log) {
            $hour = $log->created_at->hour;
            return $hour >= 22 || $hour <= 6;
        });

        if ($nightTimeLogs->count() > $logs->count() * 0.1) {
            $findings[] = 'Unusual activity detected during off-hours';
            $recommendations[] = 'Review access patterns and implement time-based restrictions if needed';
        }

        return [
            'report_type' => $reportType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'summary' => $summary,
            'findings' => $findings,
            'recommendations' => $recommendations,
            'total_logs' => $logs->count(),
        ];
    }

    /**
     * Create access review
     */
    public static function createAccessReview($userId, $reviewType, $reason = null)
    {
        return AccessReview::create([
            'user_id' => $userId,
            'review_type' => $reviewType,
            'status' => 'pending',
            'reason' => $reason,
        ]);
    }

    /**
     * Approve access review
     */
    public static function approveAccessReview($reviewId, $reviewerId, $notes = null)
    {
        $review = AccessReview::findOrFail($reviewId);
        $review->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'reason' => $notes,
        ]);

        self::log('access_review_approved', 'access_reviews', $reviewId, null, null, 
            "Access review approved for user {$review->user_id}");
    }

    /**
     * Deny access review
     */
    public static function denyAccessReview($reviewId, $reviewerId, $reason)
    {
        $review = AccessReview::findOrFail($reviewId);
        $review->update([
            'status' => 'denied',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'reason' => $reason,
        ]);

        self::log('access_review_denied', 'access_reviews', $reviewId, null, null, 
            "Access review denied for user {$review->user_id}: {$reason}");
    }

    /**
     * Get user activity summary
     */
    public static function getUserActivitySummary($userId, $days = 30)
    {
        $startDate = now()->subDays($days);
        
        $logs = AuditLog::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'total_activities' => $logs->count(),
            'login_count' => $logs->where('action', 'login_success')->count(),
            'data_changes' => $logs->whereIn('action', ['create', 'update', 'delete'])->count(),
            'last_activity' => $logs->max('created_at'),
            'most_active_table' => $logs->pluck('table_name')->mode(),
            'severity_distribution' => $logs->groupBy('severity')->map->count(),
        ];
    }

    /**
     * Get system health metrics
     */
    public static function getSystemHealthMetrics($days = 7)
    {
        $startDate = now()->subDays($days);
        
        $logs = AuditLog::where('created_at', '>=', $startDate)->get();
        
        $alerts = AuditAlert::where('created_at', '>=', $startDate)->get();
        
        return [
            'total_events' => $logs->count(),
            'active_users' => $logs->pluck('user_id')->unique()->count(),
            'security_alerts' => $alerts->where('alert_type', 'security')->count(),
            'financial_alerts' => $alerts->where('alert_type', 'financial')->count(),
            'unresolved_alerts' => $alerts->where('is_resolved', false)->count(),
            'critical_events' => $logs->where('severity', 'critical')->count(),
            'error_events' => $logs->where('severity', 'error')->count(),
        ];
    }
} 