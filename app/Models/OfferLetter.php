<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_application_id', 
        'status', 
        'token', 
        'salary_amount', 
        'salary_currency', 
        'salary_breakdown', 
        'joining_date', 
        'expiry_date', 
        'content', 
        'manual_path', 
        'attachments',
        'is_conditional',
        'document_template_id',
        'salary_structure_id',
        'designation',
        'offer_date',
        'accepted_at',
        'accepted_ip',
        'signature_image',
        'candidate_preferences',
        'approval_status',
        'approvers',
        'approval_token',
        'approval_data',
        'otp',
        'otp_expires_at',
        'email_config'
    ];

    protected $casts = [
        'salary_breakdown' => 'array',
        'attachments' => 'array',
        'candidate_preferences' => 'array',
        'offer_date' => 'date',
        'joining_date' => 'date',
        'expiry_date' => 'date',
        'accepted_at' => 'datetime',
        'is_conditional' => 'boolean',
        'approvers' => 'array',
        'approval_data' => 'array',
        'otp_expires_at' => 'datetime',
        'email_config' => 'array',
        'template_ids' => 'array'
    ];

    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function salaryStructure()
    {
        return $this->belongsTo(SalaryStructure::class, 'salary_structure_id');
    }

    public function documents()
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function versions()
    {
        return $this->hasMany(OfferVersion::class)->orderBy('version_number', 'desc');
    }

    /**
     * Create a snapshot version of the current offer.
     *
     * @param int|null $userId
     * @return OfferVersion
     */
    public function createVersion($userId = null)
    {
        $latestVersion = $this->versions()->first();
        $nextVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

        return $this->versions()->create([
            'version_number' => $nextVersionNumber,
            'payload' => $this->toArray(),
            'created_by' => $userId ?? auth()->id()
        ]);
    }

    /**
     * Get the data array for template replacement.
     */
    public function getDataAttribute()
    {
        $candidate = $this->jobApplication->candidate;
        $job = $this->jobApplication->job;

        return [
            'candidate' => [
                'name' => $candidate->first_name . ' ' . $candidate->last_name,
                'first_name' => $candidate->first_name,
                'last_name' => $candidate->last_name,
                'email' => $candidate->email,
                'phone' => $candidate->phone,
                'address' => $candidate->address ?? '',
            ],
            'job' => [
                'title' => $job->title,
                'department' => $job->department->name ?? '',
                'location' => $job->location->name ?? '',
            ],
            'offer' => [
                'id' => $this->id,
                'designation' => $this->designation,
                'joining_date' => $this->joining_date ? $this->joining_date->format('d M, Y') : '',
                'offer_date' => $this->offer_date ? $this->offer_date->format('d M, Y') : '',
                'expiry_date' => $this->expiry_date ? $this->expiry_date->format('d M, Y') : '',
                'ctc' => $this->salary_amount, // Raw CTC
                'currency' => $this->salary_currency,
            ],
            'salary' => $this->salary_breakdown, // Array of components
            'signature_image' => $this->signature_image, // Base64
            'date' => now()->format('d M, Y'), // Today
        ];
    }
}
