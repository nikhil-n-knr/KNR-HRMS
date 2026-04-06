@component('mail::message')
# Interview Reminder

@if($body)
{!! $body !!}
@else
Hello {{ $interview->application->candidate->first_name }},

This is a reminder for your upcoming interview for the **{{ $interview->application->job->title }}** position.

**Round:** {{ $interview->round }}  
**Date:** {{ $interview->scheduled_at->format('M d, Y h:i A') }}  
**Type:** {{ $interview->type }}  

Please make sure to be ready 5 minutes before the scheduled time.

@component('mail::button', ['url' => config('app.url')])
View Application
@endcomponent

Best regards,<br>
{{ config('app.name') }}
@endif

@endcomponent
