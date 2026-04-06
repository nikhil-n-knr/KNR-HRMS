<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Badge</title>
    <style>
        @page {
            size: 62mm 100mm;
            margin: 0;
        }
        body {
            width: 62mm;
            height: 100mm;
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: white;
            color: black;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .header {
            width: 100%;
            background: #4F46E5; /* Indigo-600 */
            color: white;
            text-align: center;
            padding: 4mm 0;
            font-size: 5mm;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .photo-container {
            width: 35mm;
            height: 35mm;
            margin: 5mm 0;
            border: 1mm solid #F3F4F6;
            border-radius: 4mm;
            overflow: hidden;
            background: #F9FAFB;
        }
        .photo-container img {
            width: 100%;
            height: 100%;
            object-cover: cover;
        }
        .visitor-name {
            font-size: 6mm;
            font-weight: 800;
            text-align: center;
            margin: 1mm 0;
            color: #111827;
        }
        .visitor-company {
            font-size: 3mm;
            color: #6B7280;
            font-weight: 600;
            margin-bottom: 4mm;
        }
        .host-info {
            width: 100%;
            padding: 0 4mm;
            box-sizing: border-box;
            font-size: 2.8mm;
            display: flex;
            justify-content: space-between;
            border-top: 1px dashed #E5E7EB;
            padding-top: 3mm;
        }
        .host-label {
            font-weight: 700;
            color: #9CA3AF;
            text-transform: uppercase;
        }
        .host-value {
            font-weight: 700;
            color: #374151;
        }
        .qr-section {
            margin-top: auto;
            margin-bottom: 4mm;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .qr-code {
            width: 25mm;
            height: 25mm;
            margin-bottom: 2mm;
        }
        .pass-id {
            font-family: monospace;
            font-size: 3.5mm;
            font-weight: 700;
            color: #4B5563;
        }
        .footer-banner {
            width: 100%;
            background: #F3F4F6;
            padding: 2mm 0;
            text-align: center;
            font-size: 2.5mm;
            font-weight: 700;
            color: #6B7280;
            margin-top: auto;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">Visitor Pass</div>
    
    <div class="photo-container">
        @if($visitor->photo_path)
            <img src="{{ $visitor->photo_path }}" alt="Visitor Photo">
        @else
            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#D1D5DB;">
                <svg width="24mm" height="24mm" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            </div>
        @endif
    </div>

    <div class="visitor-name">{{ $visitor->name }}</div>
    <div class="visitor-company">{{ $visitor->company ?? 'PRIVATE VISIT' }}</div>

    <div class="host-info">
        <span class="host-label">Host</span>
        <span class="host-value">{{ $visitor->host->name }}</span>
    </div>
    
    <div class="host-info" style="border-top:0; padding-top:1mm;">
        <span class="host-label">Valid On</span>
        <span class="host-value">{{ now()->format('d M Y') }}</span>
    </div>

    <div class="qr-section">
        <div class="qr-code">
            <!-- In a real app we'd use a QR generator, for now a placeholder -->
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $pass->pass_code }}" style="width:100%">
        </div>
        <div class="pass-id">ID: {{ $pass->pass_code }}</div>
    </div>

    <div class="footer-banner">
        PLEASE WEAR THIS BADGE VISIBLY AT ALL TIMES
    </div>
</body>
</html>
