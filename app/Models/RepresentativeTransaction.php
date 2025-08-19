<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepresentativeTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'representative_id', 'type', 'amount', 'transaction_date', 'reference', 'notes', 'created_by'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function representative()
    {
        return $this->belongsTo(SalesRepresentative::class, 'representative_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
} 