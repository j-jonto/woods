<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no', 'order_date', 'supplier_id', 'status', 'payment_type', 
        'total_amount', 'created_by', 'currency_id', 'exchange_rate', 
        'base_total_amount', 'base_subtotal', 'base_tax_amount', 
        'base_discount_amount', 'base_shipping_amount'
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'base_total_amount' => 'decimal:2',
        'base_subtotal' => 'decimal:2',
        'base_tax_amount' => 'decimal:2',
        'base_discount_amount' => 'decimal:2',
        'base_shipping_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
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
            ->where('reference_type', 'purchase_order');
    }

    // علاقات مع فواتير الشراء
    public function purchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class, 'purchase_order_id');
    }

    // Accessors
    public function getTotalCostAttribute()
    {
        return $this->items->sum('amount');
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
        return in_array($this->status, ['received', 'invoiced']);
    }

    public function getIsCancelledAttribute()
    {
        return $this->status === 'cancelled';
    }

    public function getIsPendingAttribute()
    {
        return in_array($this->status, ['draft', 'ordered', 'shipped']);
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

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('order_date', [$from, $to]);
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['received', 'invoiced']);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'ordered', 'shipped']);
    }
} 