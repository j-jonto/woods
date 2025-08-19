<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_date', 'reference_no', 'type', 'item_id', 'warehouse_id', 
        'quantity', 'unit_cost', 'batch_no', 'reference_type', 'reference_id', 
        'description', 'created_by'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // علاقات مع المراجع
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'reference_id')
            ->where('reference_type', 'sales_order');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'reference_id')
            ->where('reference_type', 'purchase_order');
    }

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class, 'reference_id')
            ->where('reference_type', 'production_order');
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'reference_id')
            ->where('reference_type', 'purchase_invoice');
    }

    // Accessors
    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->unit_cost;
    }

    public function getIsReceiptAttribute()
    {
        return in_array($this->type, ['receipt', 'transfer']);
    }

    public function getIsIssueAttribute()
    {
        return in_array($this->type, ['issue', 'sale', 'adjustment']);
    }

    public function getIsPositiveAttribute()
    {
        return $this->is_receipt;
    }

    public function getIsNegativeAttribute()
    {
        return $this->is_issue;
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    public function scopeReceipts($query)
    {
        return $query->whereIn('type', ['receipt', 'transfer']);
    }

    public function scopeIssues($query)
    {
        return $query->whereIn('type', ['issue', 'sale', 'adjustment']);
    }

    public function scopeByReference($query, $referenceType, $referenceId)
    {
        return $query->where('reference_type', $referenceType)
                    ->where('reference_id', $referenceId);
    }
} 