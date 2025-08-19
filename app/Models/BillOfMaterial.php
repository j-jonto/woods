<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillOfMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'finished_good_id', 
        'raw_material_id', 
        'quantity',
        'description',
        'is_active'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    public function finishedGood()
    {
        return $this->belongsTo(Item::class, 'finished_good_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(Item::class, 'raw_material_id');
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class);
    }

    // حساب التكلفة الإجمالية للمواد الخام
    public function getTotalMaterialCostAttribute()
    {
        return $this->quantity * ($this->rawMaterial->standard_cost ?? 0);
    }

    // Accessor للحالة
    public function getIsActiveAttribute($value)
    {
        return (bool) $value;
    }

    // Scope للقوائم النشطة
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope للقوائم غير النشطة
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
} 