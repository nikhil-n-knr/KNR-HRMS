<x-mail::message>
# You're Invited!

Hello {{ $name }},

We are thrilled to invite you to **{{ $eventTitle }}**. We've prepared everything for your arrival at **{{ $location }}**.

### Event Details
- **Date & Time:** {{ $startTime }}
- **Location:** {{ $location }}

### Your Fast-Track Entry QR Code
Please present this QR code at the reception or kiosk for instant check-in.

<img src="{{ $qrCode }}" alt="Invitation QR Code" style="display: block; margin: 20px auto; border-radius: 12px; border: 4px solid #f3f4f6;">

<x-mail::button :url="$link">
Confirm Your Attendance
</x-mail::button>

We look forward to seeing you!

Best regards,<br>
The Resource Management Team
</x-mail::message>
