<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'acquisition_date', 'acquisition_cost', 'useful_life', 'depreciation_method', 'status'
    ];

    public function getCurrentBookValue()
    {
        // إذا لم تتوفر البيانات الأساسية، أرجع التكلفة الأصلية
        if (!$this->acquisition_date || !$this->acquisition_cost || !$this->useful_life) {
            return $this->acquisition_cost;
        }
        $yearsUsed = now()->diffInYears(\Carbon\Carbon::parse($this->acquisition_date));
        $annualDepreciation = $this->acquisition_cost / $this->useful_life;
        $depreciation = $annualDepreciation * $yearsUsed;
        $bookValue = $this->acquisition_cost - $depreciation;
        return $bookValue > 0 ? $bookValue : 0;
    }
} 