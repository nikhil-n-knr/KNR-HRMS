<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Meeting;
use App\Models\CRM\MeetingAttendee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicMeetingController extends Controller
{
    /**
     * Show detailed session information to the guest.
     */
    public function show(string $uuid)
    {
        $meeting = Meeting::where('id', $uuid)->orWhere('link', 'like', "%{$uuid}%")->firstOrFail();
        $meeting->load(['attendees', 'employee']);

        return Inertia::render('CRM/Public/MeetingDetails', [
            'meeting' => $meeting
        ]);
    }

    /**
     * Guest RSVP Action
     */
    public function rsvp(Request $request, string $uuid)
    {
        $request->validate([
            'email' => 'required|email',
            'status' => 'required|in:accepted,declined,tentative'
        ]);

        $meeting = Meeting::where('id', $uuid)->orWhere('link', 'like', "%{$uuid}%")->firstOrFail();
        
        $attendee = MeetingAttendee::where('meeting_id', $meeting->id)
            ->where('email', $request->email)
            ->firstOrFail();

        $attendee->update(['status' => $request->status]);

        return back()->with('success', "RSVP status updated to: {$request->status}");
    }
}
