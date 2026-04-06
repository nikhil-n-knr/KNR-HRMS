<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payslip</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; margin: 0 auto; padding: 20px; position: relative; }
        .header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .company-name { font-size: 20px; font-weight: bold; color: #1a202c; }
        .payslip-title { font-size: 16px; margin-top: 5px; color: #666; }
        
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #888; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; margin-top: 20px; }
        
        .grid { display: table; width: 100%; }
        .row { display: table-row; }
        .col { display: table-cell; vertical-align: top; width: 50%; padding-right: 15px; }
        
        table.details { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.details td { padding: 5px; border-bottom: 1px solid #fafafa; }
        table.details td.label { font-weight: bold; color: #555; width: 40%; }
        table.details td.value { text-align: right; }
        
        .box { background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        
        .total-row td { border-top: 1px solid #ddd; font-weight: bold; padding-top: 10px; font-size: 13px; }
        
        .net-pay-box { background: #ebf8ff; border: 1px solid #bee3f8; padding: 15px; text-align: center; border-radius: 6px; margin-top: 30px; }
        .net-pay-label { font-size: 12px; color: #2b6cb0; text-transform: uppercase; letter-spacing: 1px; }
        .net-pay-amount { font-size: 24px; font-weight: bold; color: #2c5282; margin-top: 5px; }
        
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #aaa; border-top: 1px solid #eee; padding-top: 20px; }

        @if(isset($configs['watermark']) && $configs['watermark'])
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: -1;
            width: 80%;
            pointer-events: none;
        }
        @endif
    </style>
</head>
<body>
    @if(isset($configs['watermark']) && $configs['watermark'])
        <img src="{{ $configs['watermark'] }}" class="watermark" alt="Watermark">
    @endif

    <div class="container">
        <div class="header">
            @if(isset($configs['header']) && $configs['header'])
                {!! $configs['header'] !!}
            @else
                <div class="company-name">{{ config('app.name', 'HRMS') }}</div>
            @endif
            <div class="payslip-title">Payslip for {{ $payroll->batch_name }}</div>
        </div>

        <!-- Employee Info -->
        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td style="width: 50%">
                    <strong>Name:</strong> {{ $payslip->employee->user->name }}<br>
                    <strong>Employee ID:</strong> {{ $payslip->employee->employee_id }}<br>
                    <strong>Department:</strong> {{ $payslip->employee->department->name ?? 'N/A' }}
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>Payslip #:</strong> {{ $payslip->payslip_number }}<br>
                    <strong>Pay Period:</strong> {{ $payroll->start_date->format('d M') }} - {{ $payroll->end_date->format('d M Y') }}<br>
                    <strong>Generated On:</strong> {{ $payslip->created_at->format('d M Y') }}
                </td>
            </tr>
        </table>

        <!-- Attendance -->
        <div class="box">
            <table style="width: 100%">
                <tr>
                    <td style="width: 33%; text-align: center;">
                        <span style="display: block; font-size: 10px; color: #888;">PAYABLE DAYS</span>
                        <strong style="font-size: 14px;">{{ $payslip->payable_days }}</strong>
                    </td>
                    <td style="width: 33%; text-align: center;">
                        <span style="display: block; font-size: 10px; color: #888;">LOSS OF PAY (LOP)</span>
                        <strong style="font-size: 14px; color: #e53e3e;">{{ $payslip->lop_days }}</strong>
                    </td>
                    <td style="width: 33%; text-align: center;">
                        <span style="display: block; font-size: 10px; color: #888;">TOTAL DAYS</span>
                        <strong style="font-size: 14px;">{{ $payroll->start_date->daysInMonth }}</strong>
                    </td>
                </tr>
            </table>
        </div>

        <div class="grid">
            <div class="row">
                <!-- Earnings -->
                <div class="col">
                    <div class="section-title">Earnings</div>
                    <table class="details">
                        @foreach(($payslip->earnings_breakdown ?? []) as $label => $amount)
                        <tr>
                            <td class="label">{{ $label }}</td>
                            <td class="value">{{ number_format($amount, 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td>Total Earnings</td>
                            <td class="value">{{ number_format($payslip->gross_earnings, 2) }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Deductions -->
                <div class="col">
                    <div class="section-title">Deductions</div>
                    <table class="details">
                        @foreach(($payslip->deductions_breakdown ?? []) as $label => $amount)
                        <tr>
                            <td class="label">{{ $label }}</td>
                            <td class="value">{{ number_format($amount, 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td style="color: #e53e3e;">Total Deductions</td>
                            <td class="value" style="color: #e53e3e;">-{{ number_format($payslip->gross_deductions, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Net Pay -->
        <div class="net-pay-box">
            <div class="net-pay-label">Net Pay Transferred</div>
            <div class="net-pay-amount">INR {{ number_format($payslip->net_pay, 2) }}</div>
            <div style="margin-top: 5px; font-size: 11px; color: #718096; font-style: italic;">
                (Rupees {{ NumberFormatter::create('en_IN', NumberFormatter::SPELLOUT)->format($payslip->net_pay) }} Only)
            </div>
        </div>

        <div class="footer">
            @if(isset($configs['footer']) && $configs['footer'])
                {!! $configs['footer'] !!}
            @else
                <p>This is a system-generated payslip and does not require a signature.</p>
                <p>{{ config('app.name') }} | Confidential</p>
            @endif
        </div>
    </div>
</body>
</html>
