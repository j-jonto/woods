<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'tax_type_id',
        'taxable_amount',
        'tax_amount',
    ];

    protected $casts = [
        'taxable_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    /**
     * Get the purchase order this tax belongs to.
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the tax type.
     */
    public function taxType()
    {
        return $this->belongsTo(TaxType::class);
    }

    /**
     * Calculate tax amount based on taxable amount and tax rate.
     */
    public function calculateTaxAmount()
    {
        if ($this->taxType) {
            $this->tax_amount = $this->taxType->calculateTax($this->taxable_amount);
        }
        return $this->tax_amount;
    }
} 