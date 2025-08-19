<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptVoucher extends Model
{
    protected $fillable = [
        'account_id', 'amount', 'date', 'source', 'reference_no', 'notes', 'created_by'
    ];

    public function account()
    {
        return $this->belongsTo(CashAccount::class, 'account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
} 