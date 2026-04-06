<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class MarketingTemplate extends Model
{
    use HasFactory;

    protected $table = 'crm_marketing_templates';

    protected $fillable = [
        'tenant_id', 'name', 'subject', 'content', 'category', 'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
