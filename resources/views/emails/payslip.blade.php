<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { padding: 20px; }
        .btn { background-color: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello {{ $employee->user->name }},</h2>
        <p>Your payslip for <strong>{{ $payslip->payroll->batch_name }}</strong> is ready.</p>
        <p>Please find the payslip attached to this email.</p>
        <br>
        <p>Regards,<br>{{ config('app.name') }} HR Team</p>
    </div>
</body>
</html>
