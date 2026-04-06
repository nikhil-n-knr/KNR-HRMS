<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        @if(!empty($customBody))
            {!! $customBody !!}
            
            <p>
                <a href="{{ $url }}" class="btn">View Offer</a>
            </p>
        @else
        <h2>Job Offer</h2>
        <p>Dear {{ $offer->jobApplication->candidate->first_name }},</p>
        
        <p>We are pleased to offer you the position of <strong>{{ $offer->designation }}</strong> at {{ config('app.name') }}.</p>
        
        <p>Please click the button below to view your detailed offer letter and upload the required documents.</p>
        
        <p>
            <a href="{{ $url }}" class="btn">View Offer & Upload Documents</a>
        </p>

        <p>
            Or copy this link:<br>
            <a href="{{ $url }}">{{ $url }}</a>
        </p>
        @endif

        <p>Best Regards,<br>HR Team</p>
    </div>
</body>
</html>
