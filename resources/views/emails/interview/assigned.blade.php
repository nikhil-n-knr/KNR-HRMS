<x-mail::message>
# New Interview Assigned

You have been assigned to interview **{{ $candidateName }}**.

**Details:**
- **Date:** {{ $date }}
- **Type:** {{ $type }}
@if($meetingLink)
- **Link:** [Join Meeting]({{ $meetingLink }})
@endif

<x-mail::button :url="$candidateLink">
View Candidate Profile
</x-mail::button>

Please review the candidate's profile and prepare for the interview.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
