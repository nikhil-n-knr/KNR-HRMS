<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'deal_id', 'account_id', 'contact_id', 'created_by',
        'quote_number', 'title', 'description', 'status',
        'subtotal', 'discount', 'discount_type', 'tax_rate', 'tax_amount', 'total',
        'valid_until', 'sent_at', 'viewed_at', 'accepted_at',
        'terms', 'notes', 'template_id'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'valid_until' => 'date',
        'sent_at' => 'date',
        'viewed_at' => 'date',
        'accepted_at' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function template()
    {
        return $this->belongsTo(QuoteTemplate::class);
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class)->orderBy('order');
    }

    public function calculateTotals()
    {
        $subtotal = $this->items->sum('total');
        $discountAmount = 0;
        
        if ($this->discount > 0) {
            if ($this->discount_type === 'percentage') {
                $discountAmount = $subtotal * ($this->discount / 100);
            } else {
                $discountAmount = $this->discount;
            }
        }

        $afterDiscount = $subtotal - $discountAmount;
        $taxAmount = $afterDiscount * ($this->tax_rate / 100);
        $total = $afterDiscount + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total
        ]);
    }
}
