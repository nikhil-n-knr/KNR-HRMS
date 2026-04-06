<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Georgia', serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
        }
        .certificate {
            width: 100%;
            height: 100vh;
            padding: 60px;
            box-sizing: border-box;
            position: relative;
        }
        .certificate-content {
            background: white;
            border: 20px solid #f0f0f0;
            border-radius: 10px;
            padding: 60px;
            height: 100%;
            box-sizing: border-box;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            font-size: 48px;
            color: #667eea;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .header p {
            font-size: 18px;
            color: #666;
            margin: 10px 0 0 0;
        }
        .body {
            text-align: center;
            margin: 60px 0;
        }
        .body p {
            font-size: 20px;
            color: #555;
            margin: 20px 0;
        }
        .employee-name {
            font-size: 42px;
            font-weight: bold;
            color: #333;
            margin: 30px 0;
            text-decoration: underline;
            text-decoration-color: #667eea;
        }
        .course-title {
            font-size: 28px;
            color: #764ba2;
            font-style: italic;
            margin: 20px 0;
        }
        .details {
            margin: 40px 0;
            font-size: 16px;
            color: #666;
        }
        .details table {
            margin: 0 auto;
            border-collapse: collapse;
        }
        .details td {
            padding: 10px 20px;
            text-align: left;
        }
        .details td:first-child {
            font-weight: bold;
            color: #333;
        }
        .footer {
            position: absolute;
            bottom: 60px;
            left: 60px;
            right: 60px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .qr-code {
            text-align: left;
        }
        .qr-code img {
            width: 120px;
            height: 120px;
        }
        .qr-code p {
            font-size: 10px;
            color: #999;
            margin: 5px 0 0 0;
        }
        .signature {
            text-align: right;
        }
        .signature-line {
            border-top: 2px solid #333;
            width: 200px;
            margin: 0 0 10px auto;
        }
        .signature p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .certificate-code {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 12px;
            color: #999;
            font-family: 'Courier New', monospace;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(102, 126, 234, 0.05);
            font-weight: bold;
            z-index: 0;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="certificate-content">
            <div class="watermark">CERTIFIED</div>
            
            <div class="certificate-code">
                {{ $certificate->certificate_code }}
            </div>
            
            <div class="header">
                <h1>Certificate of Completion</h1>
                <p>This is to certify that</p>
            </div>
            
            <div class="body">
                <div class="employee-name">
                    {{ $employee->full_name }}
                </div>
                
                <p>has successfully completed the training course</p>
                
                <div class="course-title">
                    "{{ $course->title }}"
                </div>
                
                <div class="details">
                    <table>
                        <tr>
                            <td>Completion Date:</td>
                            <td>{{ $certificate->issued_on->format('F d, Y') }}</td>
                        </tr>
                        <tr>
                            <td>Score Achieved:</td>
                            <td>{{ number_format($attempt->percentage, 2) }}%</td>
                        </tr>
                        @if($certificate->expires_on)
                        <tr>
                            <td>Valid Until:</td>
                            <td>{{ $certificate->expires_on->format('F d, Y') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            
            <div class="footer">
                <div class="qr-code">
                    <img src="{{ $qr_url }}" alt="QR Code">
                    <p>Scan to verify</p>
                </div>
                
                <div class="signature">
                    <div class="signature-line"></div>
                    <p><strong>Authorized Signature</strong></p>
                    <p>HR Department</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
