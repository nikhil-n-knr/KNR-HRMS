<!DOCTYPE html>
<html>
<head>
    <title>Form 16 Part B</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; }
        .header { text-align: center; font-weight: bold; margin-bottom: 20px; }
        .sub-header { font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 5px; vertical-align: top; }
        .no-border { border: none !important; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .bg-gray { background-color: #f0f0f0; }
        .w-10 { width: 10%; }
        .w-60 { width: 60%; }
        .w-15 { width: 15%; }
    </style>
</head>
<body>

    <div class="header">
        FORM NO. 16<br>
        <span style="font-size: 10px; font-weight: normal;">[See rule 31(1)(a)]</span><br>
        PART B<br>
        <span style="font-size: 12px;">Details of Salary Paid and any other income and tax deducted</span>
    </div>

    <!-- Employee Details -->
    <table>
        <tr>
            <td class="bold bg-gray" colspan="2">Details of Employee</td>
        </tr>
        <tr>
            <td width="30%">Name</td>
            <td class="bold">{{ $employee->first_name }} {{ $employee->last_name }}</td>
        </tr>
        <tr>
            <td>PAN</td>
            <td class="bold">{{ $employee->pan_number ?? 'INVALID' }}</td>
        </tr>
        <tr>
            <td>Assessment Year</td>
            <td class="bold">{{ $fy }}</td>
        </tr>
    </table>

    <!-- Computation -->
    <table>
        <tr class="bg-gray">
            <th class="w-10">S.No.</th>
            <th class="w-60">Particulars</th>
            <th class="w-15">Gross (Rs.)</th>
            <th class="w-15">Deductible (Rs.)</th>
        </tr>

        <!-- 1. Gross Salary -->
        <tr>
            <td>1.</td>
            <td class="bold">Gross Salary</td>
            <td class="right bold">{{ number_format($gross_salary, 2) }}</td>
            <td></td>
        </tr>
        <tr>
            <td>(a)</td>
            <td>Salary as per provisions contained in sec. 17(1)</td>
            <td class="right">{{ number_format($gross_salary, 2) }}</td>
            <td></td>
        </tr>
        <tr>
            <td>(b)</td>
            <td>Value of perquisites u/s 17(2)</td>
            <td class="right">0.00</td>
            <td></td>
        </tr>
        <tr>
            <td>(c)</td>
            <td>Profits in lieu of salary u/s 17(3)</td>
            <td class="right">0.00</td>
            <td></td>
        </tr>

        <!-- 2. Allowances -->
        <tr>
            <td>2.</td>
            <td class="bold">Less: Allowances to the extent exempt u/s 10</td>
            <td></td>
            <td class="right bold">{{ number_format($hra_exemption, 2) }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Sec 10(13A) HRA</td>
            <td class="right">{{ number_format($hra_exemption, 2) }}</td>
            <td></td>
        </tr>

        <!-- 3. Balance -->
        <tr>
            <td>3.</td>
            <td class="bold">Balance (1 - 2)</td>
            <td class="right bold">{{ number_format($gross_salary - $hra_exemption, 2) }}</td>
            <td></td>
        </tr>

        <!-- 4. Deductions under Chapter VI-A -->
        <tr>
            <td>4.</td>
            <td class="bold">Deductions under Chapter VI-A</td>
            <td></td>
            <td></td>
        </tr>
        
        <tr>
            <td>(a)</td>
            <td>Standard Deduction u/s 16(ia)</td>
            <td></td>
            <td class="right">{{ number_format($standard_deduction, 2) }}</td>
        </tr>
         <tr>
            <td>(b)</td>
            <td>Deductions 80C, 80D, etc.</td>
            <td></td>
            <td class="right">{{ number_format($chapter_6a, 2) }}</td>
        </tr>

        <!-- 5. Total Taxable Income -->
        <tr>
            <td>5.</td>
            <td class="bold">Total Taxable Income (3 - 4)</td>
            <td class="right bold bg-gray">{{ number_format($taxable_income, 2) }}</td>
            <td></td>
        </tr>

        <!-- 6. Tax On Total Income -->
         <tr>
            <td>6.</td>
            <td>Tax on Total Income</td>
            <td class="right">{{ number_format($tax_payable, 2) }}</td>
            <td></td>
        </tr>
         <tr>
            <td>7.</td>
            <td>Education Cess @ 4%</td>
            <td class="right">{{ number_format($tax_payable * 0.04, 2) }}</td>
            <td></td>
        </tr>
         <tr>
            <td>8.</td>
            <td class="bold">Tax Payable (6+7)</td>
            <td class="right bold">{{ number_format($tax_payable * 1.04, 2) }}</td>
            <td></td>
        </tr>
          <tr>
            <td>9.</td>
            <td class="bold">Less: Tax Deducted at Source (TDS)</td>
            <td class="right bold">{{ number_format($tax_paid, 2) }}</td>
            <td></td>
        </tr>
         <tr>
            <td>10.</td>
            <td class="bold">Tax Payable / (Refundable) (8-9)</td>
            <td class="right bold">{{ number_format(($tax_payable * 1.04) - $tax_paid, 2) }}</td>
            <td></td>
        </tr>

    </table>

    <div style="margin-top: 40px;">
        <p class="bold">Verification</p>
        <p>I, <span class="bold">Admin</span>, son/daughter of <span class="bold">Admin Father</span>, working in the capacity of <span class="bold">Authorized Signatory</span> do hereby certify that the information given above is true and correct based on the book of accounts, documents, and other available records.</p>
        
        <br><br>
        <table>
            <tr class="no-border">
                <td class="no-border">Place: __________________</td>
                <td class="no-border right">Signature: __________________</td>
            </tr>
            <tr class="no-border">
                <td class="no-border">Date: {{ $generated_date }}</td>
                <td class="no-border right">Full Info: Authorized Signatory</td>
            </tr>
        </table>
    </div>

</body>
</html>
