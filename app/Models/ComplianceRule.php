<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplianceRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'component',
        'effective_from',
        'rules_json',
        'is_active',
        'description'
    ];

    protected $casts = [
        'rules_json' => 'array',
        'effective_from' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Get the active rule for a component on a specific date.
     */
    public static function getRule(string $component, $date = null)
    {
        $date = $date ?: now();

        return self::where('component', $component)
            ->where('is_active', true)
            ->where('effective_from', '<=', $date)
            ->orderBy('effective_from', 'desc')
            ->first();
    }
}
