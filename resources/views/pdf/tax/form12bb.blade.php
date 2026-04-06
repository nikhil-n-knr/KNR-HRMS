<!DOCTYPE html>
<html>
<head>
    <title>Form 12BB</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 16px; font-weight: bold; text-decoration: underline; margin-bottom: 5px; }
        .subtitle { font-size: 14px; font-weight: bold; }
        .section-header { background: #eee; padding: 5px; font-weight: bold; border: 1px solid #000; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 5px; vertical-align: top; }
        .no-border { border: none; }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">FORM NO. 12BB</div>
        <div class="subtitle">(See rule 26C)</div>
        <div>Statement for Claims by Employee for Deduction of Tax under section 192</div>
    </div>

    <table>
        <tr>
            <td width="30%">1. Name and Address of the Employee:</td>
            <td>
                <strong>{{ $employee->user->name }} ({{ $employee->employee_code }})</strong><br>
                {{ $employee->address ?? 'Address not updated' }}
            </td>
        </tr>
        <tr>
            <td>2. Permanent Account Number (PAN):</td>
            <td>{{ $employee->pan_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>3. Financial Year:</td>
            <td>{{ $fiscalYear }}</td>
        </tr>
    </table>

    <div class="section-header">House Rent Allowance (Section 10(13A))</div>
    <table>
        <thead>
            <tr>
                <th>Rent Paid to Landlord</th>
                <th>Name of Landlord</th>
                <th>Address of Landlord</th>
                <th>Landlord PAN</th>
                <th>Amount Claimed</th>
            </tr>
        </thead>
        <tbody>
            @if($hra)
            <tr>
                <td>Rs. {{ number_format($hra->rent_monthly * 12) }}</td>
                <td>{{ $hra->landlord_name }}</td>
                <td>{{ $hra->rented_address }}</td>
                <td>{{ $hra->landlord_pan ?? 'N/A' }}</td>
                <td>Rs. {{ number_format($hra->rent_monthly * 12) }}</td>
            </tr>
            @else
            <tr><td colspan="5" style="text-align:center">No HRA Claimed</td></tr>
            @endif
        </tbody>
    </table>

    <div class="section-header">Leave Travel Concessions or Assistance (Section 10(5))</div>
    <table>
         <tr>
             <td>Amount Claimed</td>
             <td>N/A (LTA Module Pending)</td>
         </tr>
    </table>

    <div class="section-header">Deduction of Interest on Borrowing (Home Loan)</div>
    <table>
         <tr>
             <th width="70%">Particulars</th>
             <th>Amount</th>
         </tr>
         <tr>
             <td>Interest payable/paid to the lender (Section 24(b))</td>
             <td>Rs. {{ number_format($deductions['24b']) }}</td>
         </tr>
         <tr>
             <td>Name & Address of Lender / PAN</td>
             <td>Refer to uploaded proofs</td>
         </tr>
    </table>

    <div class="section-header">Deductions under Chapter VI-A</div>
    <table>
        <thead>
            <tr>
                <th>Section</th>
                <th>Nature of Claim</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>80C, 80CCC, 80CCD</td>
                <td>PF, PPF, LIC, ELSS, Tuition Fees etc.</td>
                <td>Rs. {{ number_format($deductions['80C']) }}</td>
            </tr>
             <tr>
                <td>80D</td>
                <td>Health Insurance Premia</td>
                <td>Rs. {{ number_format($deductions['80D']) }}</td>
            </tr>
            <tr>
                <td>80E</td>
                <td>Interest on Loan for Higher Education</td>
                <td>Rs. {{ number_format($deductions['80E']) }}</td>
            </tr>
             <tr>
                <td>80G</td>
                <td>Donations</td>
                <td>Rs. {{ number_format($deductions['80G']) }}</td>
            </tr>
             <tr>
                <td>Other Sections</td>
                <td>Various</td>
                <td>Rs. {{ number_format($deductions['other']) }}</td>
            </tr>
            <tr style="background:#eee">
                <td><strong>Total Chapter VI-A</strong></td>
                <td></td>
                <td><strong>Rs. {{ number_format(array_sum($deductions) - $deductions['24b']) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <br><br>
    <div>
        <strong>Verification</strong><br>
        I, <strong>{{ $employee->user->name }}</strong>, son/daughter of <strong>{{ $employee->father_name ?? '_______________' }}</strong>, do hereby certify that the information given above is complete and correct.
    </div>

    <br><br><br>
    <table>
        <tr class="no-border">
            <td class="no-border" width="50%">
                Place: __________________<br><br>
                Date: {{ $date }}
            </td>
            <td class="no-border" style="text-align:right">
                <br><br>
                (Signature of the Employee)<br>
                Designation: {{ $employee->designation->name ?? 'N/A' }}
            </td>
        </tr>
    </table>

</body>
</html>
