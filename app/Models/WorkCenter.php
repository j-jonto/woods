<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class);
    }

    // Accessors
    public function getIsActiveAttribute()
    {
        return $this->attributes['is_active'] ?? true;
    }

    public function getActiveProductionOrdersAttribute()
    {
        return $this->productionOrders()->whereIn('status', ['released', 'in_progress'])->count();
    }

    public function getCompletedProductionOrdersAttribute()
    {
        return $this->productionOrders()->where('status', 'completed')->count();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('is_active', $status === 'active');
    }
} 