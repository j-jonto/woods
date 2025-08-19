<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_type',
        'severity',
        'title',
        'description',
        'alert_data',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'alert_data' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the user who resolved the alert.
     */
    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope for unresolved alerts.
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope for resolved alerts.
     */
    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    /**
     * Scope for alerts by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Scope for alerts by severity.
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for critical alerts.
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope for high severity alerts.
     */
    public function scopeHigh($query)
    {
        return $query->where('severity', 'high');
    }

    /**
     * Scope for medium severity alerts.
     */
    public function scopeMedium($query)
    {
        return $query->where('severity', 'medium');
    }

    /**
     * Scope for low severity alerts.
     */
    public function scopeLow($query)
    {
        return $query->where('severity', 'low');
    }

    /**
     * Mark alert as resolved.
     */
    public function resolve($userId = null, $notes = null)
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $userId,
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Mark alert as unresolved.
     */
    public function unresolve()
    {
        $this->update([
            'is_resolved' => false,
            'resolved_at' => null,
            'resolved_by' => null,
            'resolution_notes' => null,
        ]);
    }

    /**
     * Get alert type label.
     */
    public function getAlertTypeLabelAttribute()
    {
        $types = [
            'security' => 'الأمان',
            'financial' => 'مالي',
            'operational' => 'تشغيلي',
            'inventory' => 'المخزون',
        ];

        return $types[$this->alert_type] ?? $this->alert_type;
    }

    /**
     * Get severity label.
     */
    public function getSeverityLabelAttribute()
    {
        $severities = [
            'low' => 'منخفض',
            'medium' => 'متوسط',
            'high' => 'عالي',
            'critical' => 'حرج',
        ];

        return $severities[$this->severity] ?? $this->severity;
    }

    /**
     * Get severity color class.
     */
    public function getSeverityColorAttribute()
    {
        $colors = [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark',
        ];

        return $colors[$this->severity] ?? 'secondary';
    }

    /**
     * Check if alert is critical.
     */
    public function isCritical()
    {
        return $this->severity === 'critical';
    }

    /**
     * Check if alert is high severity.
     */
    public function isHigh()
    {
        return $this->severity === 'high';
    }

    /**
     * Check if alert is resolved.
     */
    public function isResolved()
    {
        return $this->is_resolved;
    }
} 