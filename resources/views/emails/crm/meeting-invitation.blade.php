@component('mail::message')
# Session Invitation: {{ $meeting->title }}

You have been invited to a session. You can manage your RSVP and view full details [here]({{ route('crm.meetings.public.show', ['uuid' => $meeting->uuid]) }}).

- **Start Time:** {{ $startTime }}
- **Infrastructure:** Live Stream Active

@isset($joinUrl)
@component('mail::button', ['url' => $joinUrl])
Join Now
@endcomponent
@endisset

Thanks,<br>
{{ config('app.name') }}
@endcomponent
