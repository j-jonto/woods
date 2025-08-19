<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit',
        'credit',
        'description',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    /**
     * Get the journal entry this item belongs to.
     */
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Get the account this item is posted to.
     */
    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    // إزالة منطق amount/entry_type والاعتماد على debit/credit فقط
} 