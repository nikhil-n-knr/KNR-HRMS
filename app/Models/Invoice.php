<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function lineItems()
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
