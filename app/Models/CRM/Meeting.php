<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Employee;
use App\Models\CRM\Contact;
use App\Models\CRM\Lead;
use App\Models\CRM\Activity;
use App\Models\CRM\EmailAccount;

class Meeting extends Model
{
    use SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'scheduled';
    const STATUS_DECLINED = 'declined';
    const STATUS_TENTATIVE = 'tentative';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_NOSHOW = 'no_show';

    const PROVIDER_ZOOM = 'zoom';
    const PROVIDER_MEET = 'meet';
    const PROVIDER_TEAMS = 'teams';

    protected $table = 'crm_meetings';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'created_by',
        'assigned_to',
        'employee_id',
        'client_id',
        'meeting_type_id',
        'trackable_type',
        'trackable_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'type',
        'provider',
        'link',
        'status',
        'location',
        'reminders_config',
        'buffer_before',
        'buffer_after',
        'recurring_rule',
        'capacity',
        'booking_link',
        'sender_account_id',
        'timezone',
        'rescheduled_from_id'
    ];

    protected $casts = [
        'reminders_config' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Core Relations
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class, 'trackable_id')->where('trackable_type', 'Project');
    }

    public function attendees()
    {
        return $this->hasMany(MeetingAttendee::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function senderAccount()
    {
        return $this->belongsTo(EmailAccount::class, 'sender_account_id');
    }

    // Attendance Management
    public function syncAttendees(array $attendeeList): void
    {
        $this->attendees()->delete();
        foreach ($attendeeList as $a) {
            $this->attendees()->create([
                'attendee_id' => $a['id'],
                'attendee_type' => $a['type'] ?? 'Contact',
                'email' => $a['email'] ?? null,
                'status' => 'pending'
            ]);
        }
    }
}
