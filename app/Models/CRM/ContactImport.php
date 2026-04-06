<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ContactImport extends Model
{
    use HasFactory;

    protected $table = 'crm_contact_imports';

    protected $fillable = [
        'tenant_id', 'file_name', 'file_path', 'status', 
        'total_records', 'processed_records', 'failed_records', 
        'errors', 'imported_by'
    ];

    protected $casts = [
        'errors' => 'array',
    ];

    public function importer()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
