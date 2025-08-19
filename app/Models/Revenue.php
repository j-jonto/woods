<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    protected $fillable = [
        'revenue_no', 'revenue_type_id', 'amount', 'revenue_date', 'description', 
        'reference_no', 'reference_type', 'reference_id', 'treasury_id', 'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'revenue_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($revenue) {
            if (empty($revenue->revenue_no)) {
                $revenue->revenue_no = 'REV-' . time() . '-' . rand(1000, 9999);
            }
        });
    }

    public function revenueType()
    {
        return $this->belongsTo(RevenueType::class, 'revenue_type_id');
    }

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRelatedModel()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        $modelClass = 'App\\Models\\' . ucfirst($this->reference_type);
        
        if (class_exists($modelClass)) {
            return $modelClass::find($this->reference_id);
        }

        return null;
    }

    // Scopes
    public function scopeByType($query, $typeId)
    {
        return $query->where('revenue_type_id', $typeId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('revenue_date', [$startDate, $endDate]);
    }

    public function scopeByTreasury($query, $treasuryId)
    {
        return $query->where('treasury_id', $treasuryId);
    }
} 