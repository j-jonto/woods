<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no', 'order_date', 'delivery_date', 'customer_id', 'status', 
        'payment_type', 'representative_id', 'total_amount', 'notes', 'created_by',
        'currency_id', 'exchange_rate', 'base_total_amount', 'base_subtotal',
        'base_tax_amount', 'base_discount_amount', 'base_shipping_amount'
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'base_total_amount' => 'decimal:2',
        'base_subtotal' => 'decimal:2',
        'base_tax_amount' => 'decimal:2',
        'base_discount_amount' => 'decimal:2',
        'base_shipping_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function representative()
    {
        return $this->belongsTo(SalesRepresentative::class, 'representative_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    // علاقات مع معاملات المخزون
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'reference_id')
            ->where('reference_type', 'sales_order');
    }

    // Accessors
    public function getTotalCostAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * ($item->item->standard_cost ?? 0);
        });
    }

    public function getTotalProfitAttribute()
    {
        return $this->total_amount - $this->total_cost;
    }

    public function getProfitMarginAttribute()
    {
        if ($this->total_amount > 0) {
            return ($this->total_profit / $this->total_amount) * 100;
        }
        return 0;
    }

    public function getIsPaidAttribute()
    {
        return $this->payment_type === 'cash';
    }

    public function getIsCreditAttribute()
    {
        return $this->payment_type === 'credit';
    }

    public function getIsCompletedAttribute()
    {
        return in_array($this->status, ['delivered', 'invoiced']);
    }

    public function getIsCancelledAttribute()
    {
        return $this->status === 'cancelled';
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentType($query, $paymentType)
    {
        return $query->where('payment_type', $paymentType);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByRepresentative($query, $representativeId)
    {
        return $query->where('representative_id', $representativeId);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('order_date', [$from, $to]);
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['delivered', 'invoiced']);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'confirmed', 'shipped']);
    }
} 