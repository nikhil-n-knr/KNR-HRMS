@component('mail::message')
# Interview Invitation

@if($body)
{!! $body !!}
@else
Hello {{ $interview->application->candidate->first_name }},

We would like to invite you to an interview for the **{{ $interview->application->job->title }}** position.

**Round:** {{ $interview->round }} {{ $interview->round_title ? "({$interview->round_title})" : '' }}  
**Date:** {{ $interview->scheduled_at->format('M d, Y h:i A') }}  
**Duration:** {{ $interview->duration }} minutes  
**Type:** {{ $interview->type }}  
@if($interview->type === 'Online')
**Link:** [Join Meeting]({{ $interview->meeting_link }})
@elseif($interview->location)
**Location:** {{ $interview->location }}
@endif

Please confirm your availability.

@component('mail::button', ['url' => config('app.url')])
View Application
@endcomponent

Best regards,<br>
{{ config('app.name') }}
@endif

@endcomponent
