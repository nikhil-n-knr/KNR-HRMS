<?php

namespace App\Models\LMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LmsTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'subscription_id', 'amount', 'currency',
        'payment_provider', 'transaction_reference', 'status', 'response_data',
    ];

    protected $casts = [
        'response_data' => 'array',
        'amount'        => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(LmsSubscription::class);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'successful');
    }
}
