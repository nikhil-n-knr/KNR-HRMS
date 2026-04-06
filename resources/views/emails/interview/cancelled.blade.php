<x-mail::message>
# Interview Cancelled

The interview with **{{ $candidateName }}** scheduled for **{{ $date }}** has been cancelled.

**Reason:**
{{ $reason }}

If you need to reschedule, please visit the candidate's profile.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
