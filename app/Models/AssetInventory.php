<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetInventory extends Model
{
    protected $fillable = [
        'asset_id', 'location', 'status', 'inventory_date', 'notes'
    ];

    public function asset()
    {
        return $this->belongsTo(FixedAsset::class, 'asset_id');
    }
} 