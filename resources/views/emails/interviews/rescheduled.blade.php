@component('mail::message')
# Interview Rescheduled

@if($body)
{!! $body !!}
@else
Hello {{ $interview->application->candidate->first_name }},

Your interview for the **{{ $interview->application->job->title }}** position has been rescheduled.

**New Date:** {{ $interview->scheduled_at->format('M d, Y h:i A') }}  
**Duration:** {{ $interview->duration }} minutes  
**Type:** {{ $interview->type }}  

We apologize for any inconvenience.

@component('mail::button', ['url' => config('app.url')])
View Details
@endcomponent

Best regards,<br>
{{ config('app.name') }}
@endif

@endcomponent
