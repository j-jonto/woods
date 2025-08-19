<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $fillable = [
        'payment_no', 'supplier_id', 'purchase_invoice_id', 'amount', 'payment_date', 
        'payment_method', 'notes', 'created_by'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // علاقات مع المراجع
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'reference_id')
            ->where('reference_type', 'purchase_order');
    }

    // Accessors
    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    public function getIsPaidAttribute()
    {
        return $this->status === 'paid';
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2) . ' دينار';
    }
} 