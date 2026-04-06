<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Computation Sheet</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 0; color: #2563eb; }
        .header p { margin: 5px 0; font-size: 12px; color: #666; }
        
        .section-title { font-weight: bold; background: #f3f4f6; padding: 8px; margin-top: 20px; border-left: 4px solid #2563eb; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { text-transform: uppercase; font-size: 11px; color: #6b7280; }
        
        .amount { text-align: right; font-family: monospace; font-weight: bold; }
        .total-row { background: #eff6ff; font-weight: bold; }
        .total-row td { border-top: 2px solid #2563eb; }
        
        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-row { display: table-row; }
        .info-cell { display: table-cell; width: 50%; padding: 5px; }
        
        .regime-badge { padding: 4px 8px; background: #dbeafe; color: #1e40af; border-radius: 99px; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Income Tax Computation Sheet</h2>
        <p>Fiscal Year: {{ $fiscal_year }}</p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <strong>Employee:</strong> {{ $employee->user->name }}<br>
                <strong>Code:</strong> {{ $employee->employee_code }}<br>
                <strong>PAN:</strong> {{ $employee->pan_number ?? 'N/A' }}
            </div>
            <div class="info-cell" style="text-align: right;">
                <span class="regime-badge">{{ strtoupper($regime) }} TAX REGIME</span><br>
                <span style="font-size: 11px; color: #666;">Generated on {{ now()->format('d M Y') }}</span>
            </div>
        </div>
    </div>

    <div class="section-title">1. Gross Salary (Projected)</div>
    <table>
        <tr>
            <td>Annual Gross Salary (CTC Pro-rated)</td>
            <td class="amount">{{ number_format($annual_gross, 2) }}</td>
        </tr>
    </table>

    <div class="section-title">2. Deductions (Chapter VI-A)</div>
    @if(count($deductions) > 0 || $std_deduction > 0)
        <table>
            <thead>
                <tr>
                    <th>Section</th>
                    <th>Description</th>
                    <th class="amount">Declared</th>
                    <th class="amount">Eligible Deduction</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>u/s 16(ia)</td>
                    <td>Standard Deduction</td>
                    <td class="amount">-</td>
                    <td class="amount">{{ number_format($std_deduction, 2) }}</td>
                </tr>
                @foreach($deductions as $ded)
                <tr>
                    <td>{{ $ded['section'] }}</td>
                    <td>{{ $ded['name'] }}</td>
                    <td class="amount">{{ number_format($ded['declared'], 2) }}</td>
                    <td class="amount">{{ number_format($ded['accepted'], 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3">Total Deductions</td>
                    <td class="amount">{{ number_format($total_deductions, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #888; font-style: italic; padding: 10px;">No deductions applicable in {{ $regime }} Regime.</p>
    @endif

    <div class="section-title">3. Tax Calculation</div>
    <table>
        <tr>
            <td>Total Taxable Income (1 - 2)</td>
            <td class="amount" style="font-size: 16px;">{{ number_format($taxable_income, 2) }}</td>
        </tr>
        <tr>
            <td>Tax Liability (on Slabs)</td>
            <td class="amount">{{ number_format(($tax_payable / 1.04), 2) }}</td>
        </tr>
        <tr>
            <td>Health & Education Cess (4%)</td>
            <td class="amount">{{ number_format(($tax_payable - ($tax_payable / 1.04)), 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Total Annual Tax Payable</td>
            <td class="amount" style="color: #dc2626;">{{ number_format($tax_payable, 2) }}</td>
        </tr>
        <tr>
            <td>Monthly TDS Deduction</td>
            <td class="amount">{{ number_format($tax_payable / 12, 2) }}</td>
        </tr>
    </table>

    <div style="margin-top: 40px; border-top: 1px dashed #ccc; padding-top: 10px; font-size: 11px; text-align: center; color: #999;">
        This is a computer-generated document. No signature required.<br>
        Income Tax Projections are based on current salary structure and declarations. Actual tax may vary.
    </div>

</body>
</html>
