<!DOCTYPE html>
<html>
<head>
    <title>Form 16 Part B</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .title { font-size: 16px; font-weight: bold; }
        .subtitle { font-size: 14px; margin-top: 5px; }
        .section-title { font-weight: bold; margin-top: 15px; margin-bottom: 5px; background: #eee; padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #333; padding: 5px; text-align: left; }
        .amount { text-align: right; }
        .total-row { font-weight: bold; background: #f9f9f9; }
        .footer { margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">FORM NO. 16</div>
        <div class="subtitle">[See rule 31(1)(a)]</div>
        <div class="subtitle">PART B</div>
        <div>Certificate under section 203 of the Income-tax Act, 1961 for tax deducted at source on salary</div>
    </div>

    <table>
        <tr>
            <td colspan="2"><strong>Certificate Number:</strong> {{ $year }}-{{ $employee->employee_code }}</td>
            <td colspan="2"><strong>Last Updated:</strong> {{ $generated_at }}</td>
        </tr>
        <tr>
            <td colspan="4" class="section-title">Details of Salary Paid and any other income and tax deducted</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Name and Address of Employer</strong><br>{{ $employer_name }}<br>India</td>
            <td colspan="2"><strong>Name and Designation of Employee</strong><br>{{ $employee->first_name }} {{ $employee->last_name }}<br>{{ $employee->designation }}</td>
        </tr>
        <tr>
            <td><strong>PAN of Deductor</strong><br>{{ $employer_pan }}</td>
            <td><strong>TAN of Deductor</strong><br>{{ $employer_tan }}</td>
            <td colspan="2"><strong>PAN of Employee</strong><br>{{ $employee->pan_number ?? 'NOT AVAILABLE' }}</td>
        </tr>
        <tr>
            <td><strong>Assessment Year</strong><br>{{ explode('-', $year)[1] }}-{{ explode('-', $year)[1] + 1 }}</td>
            <td><strong>Period with the Employer</strong><br>Apr {{ explode('-', $year)[0] }} to Mar {{ explode('-', $year)[1] }}</td>
            <td colspan="2"><strong>Quarter</strong><br>Q4</td>
        </tr>
    </table>

    <div class="section-title">1. Gross Salary</div>
    <table>
        <tr>
            <th>Description</th>
            <th class="amount">Amount (Rs.)</th>
        </tr>
        <tr>
            <td>(a) Salary as per provisions contained in sec. 17(1)</td>
            <td class="amount">{{ number_format($gross_salary, 2) }}</td>
        </tr>
        <tr>
            <td>(b) Value of perquisites u/s 17(2)</td>
            <td class="amount">0.00</td>
        </tr>
        <tr>
            <td>(c) Profits in lieu of salary u/s 17(3)</td>
            <td class="amount">0.00</td>
        </tr>
        <tr class="total-row">
            <td>Total Gross Salary (1a + 1b + 1c)</td>
            <td class="amount">{{ number_format($gross_salary, 2) }}</td>
        </tr>
    </table>

    <div class="section-title">2. Allowances to the extent exempt u/s 10</div>
    <table>
         @foreach($exemptions as $name => $val)
         @if($val > 0)
         <tr>
             <td>{{ $name }}</td>
             <td class="amount">{{ number_format($val, 2) }}</td>
         </tr>
         @endif
         @endforeach
         <tr class="total-row">
             <td>Total Exemptions</td>
             <td class="amount">{{ number_format(array_sum($exemptions), 2) }}</td>
         </tr>
    </table>

    <div class="section-title">3. Deductions under Chapter VI-A</div>
    <table>
        <tr>
            <th>Section</th>
            <th class="amount">Gross Amount</th>
            <th class="amount">Deductible Amount</th>
        </tr>
        @php $totalDed = 0; @endphp
        @foreach($deductions as $sec => $val)
        <tr>
            <td>{{ $sec }}</td>
            <td class="amount">{{ number_format($val, 2) }}</td>
            <td class="amount">{{ number_format($val, 2) }}</td>
        </tr>
        @php $totalDed += $val; @endphp
        @endforeach
        <tr class="total-row">
            <td colspan="2">Total Deductions</td>
            <td class="amount">{{ number_format($totalDed, 2) }}</td>
        </tr>
    </table>

    <div class="section-title">4. Tax Payable</div>
    <table>
        @php 
            $taxableIncome = $gross_salary - array_sum($exemptions) - $totalDed;
            // Simplified logic as actual calculation is complex. Service should pass final 'tax_payable'
            // Using passed 'tax_paid' as proxy for MVP display if payable not passed
            $payable = $tax_payable ?? $tax_paid; 
        @endphp
        <tr>
            <td>Total Taxable Income</td>
            <td class="amount">{{ number_format($taxableIncome, 2) }}</td>
        </tr>
        <tr>
            <td>Tax on Total Income</td>
            <td class="amount">{{ number_format($payable, 2) }}</td>
        </tr>
        <tr>
            <td>Education Cess</td>
            <td class="amount">{{ number_format($payable * 0.04, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Total Tax Payable</td>
            <td class="amount">{{ number_format($payable * 1.04, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Less: Tax Deducted at Source</td>
            <td class="amount">{{ number_format($tax_paid, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Net Tax Payable / (Refundable)</td>
            <td class="amount">{{ number_format(($payable * 1.04) - $tax_paid, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        <p><strong>Verification</strong></p>
        <p>I, _________________________, son/daughter of _________________________, working in the capacity of _________________________ do hereby certify that the information given above is true, complete and correct based on the books of account, documents, and other available records.</p>
        <br><br>
        <p>Place: ............................  Date: {{ $generated_at }}</p>
        <p style="text-align: right;">(Signature of person responsible for deduction of tax)</p>
        <p style="text-align: right;"><strong>Full Name: ............................................</strong></p>
    </div>
</body>
</html>
