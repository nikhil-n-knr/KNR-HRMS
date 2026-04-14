<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WorkflowInstance;
use App\Models\WorkflowApproval;
use App\Services\WorkflowService;

$service = app(WorkflowService::class);

// Find staggered timesheet instances with no approvals
$stalled = WorkflowInstance::where('entity_type', 'timesheet')
    ->where('status', 'pending')
    ->get()
    ->filter(function($instance) {
        return WorkflowApproval::where('workflow_instance_id', $instance->id)->count() === 0;
    });

echo "Found " . $stalled->count() . " stalled instances." . PHP_EOL;

foreach($stalled as $instance) {
    echo "Processing Instance " . $instance->id . " for entity " . $instance->entity_id . PHP_EOL;
    $stage = $instance->currentStage;
    if ($stage) {
        // This is private, but we can potentially use Reflection or just trigger initializeWorkflow again if we delete old?
        // Better: Since it's a script, we can just call the logic manually or use a helper.
        // Actually, if I just call initializeWorkflow again, it might create duplicates.
        // Let's just use reflection to call createApprovalsForStage.
        
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('createApprovalsForStage');
        $method->setAccessible(true);
        $method->invoke($service, $instance, $stage, $instance->initiator);
    }
}
echo "Done." . PHP_EOL;
