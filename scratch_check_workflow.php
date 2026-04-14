<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\Timesheet;
use App\Models\WorkflowInstance;
use App\Models\WorkflowApproval;

$employees = Employee::with('user', 'manager')->get();
foreach($employees as $e) {
    echo "Employee: " . $e->id . " | " . ($e->user->name ?? 'N/A') . " | Manager: " . ($e->manager ? $e->manager->user->name : 'None') . PHP_EOL;
}

$recentInstances = WorkflowInstance::where('entity_type', 'timesheet')->latest()->take(10)->get();
foreach($recentInstances as $instance) {
    $approvals = WorkflowApproval::where('workflow_instance_id', $instance->id)->get();
    echo "Timesheet Instance: " . $instance->id . " | Entity ID: " . $instance->entity_id . " | Status: " . $instance->status . " | Approvals Count: " . $approvals->count() . PHP_EOL;
}
