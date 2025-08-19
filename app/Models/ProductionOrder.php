<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no', 'order_date', 'item_id', 'bill_of_material_id', 'work_center_id', 
        'quantity', 'actual_quantity', 'total_cost', 'unit_cost', 'status', 
        'start_date', 'end_date', 'notes', 'created_by'
    ];

    protected $casts = [
        'order_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'total_cost' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function billOfMaterial()
    {
        return $this->belongsTo(BillOfMaterial::class, 'bill_of_material_id');
    }

    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class, 'work_center_id');
    }

    public function materials()
    {
        return $this->hasMany(ProductionMaterial::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getIsInProgressAttribute()
    {
        return $this->status === 'in_progress';
    }

    public function getIsReleasedAttribute()
    {
        return $this->status === 'released';
    }
} 