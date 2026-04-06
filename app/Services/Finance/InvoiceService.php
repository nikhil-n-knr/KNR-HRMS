<?php

namespace App\Services\Finance;

use App\Models\Project;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class InvoiceService
{
    /**
     * Preview an invoice for a project.
     * Calculates total from all 'Done' and unbilled tasks.
     * 
     * @return array
     */
    public function preview($projectId)
    {
        $project = Project::with('client')->findOrFail($projectId);
        
        // Fetch tasks
        $tasks = Task::where('project_id', $projectId)
            ->where('status', 'done')
            ->where('is_billable', true)
            ->whereNull('billed_at')
            ->get();

        $lineItems = $tasks->map(function ($task) use ($project) {
            $rate = $project->hourly_rate ?? 0;
            $hours = $task->actual_hours > 0 ? $task->actual_hours : $task->estimated_hours;
            $amount = $hours * $rate;

            return [
                'task_id' => $task->id,
                'description' => $task->title ?: $task->name,
                'quantity' => $hours,
                'rate' => $rate,
                'amount' => $amount
            ];
        });

        $total = $lineItems->sum('amount');

        return [
            'client' => $project->client,
            'project' => $project,
            'items' => $lineItems,
            'total' => $total,
            'currency' => $project->currency ?? 'USD'
        ];
    }

    /**
     * Generate actual invoice and lock tasks.
     */
    public function generate($projectId)
    {
        return DB::transaction(function () use ($projectId) {
            $preview = $this->preview($projectId);
            $project = $preview['project'];
           
            if ($preview['items']->isEmpty()) {
                throw new \Exception("No billable items found.");
            }

            // Create Invoice
            $invoice = Invoice::create([
                'client_id' => $project->client_id,
                'project_id' => $project->id,
                'due_date' => Carbon::now()->addDays(30), // standard n30
                'total' => $preview['total'],
                'status' => 'draft'
            ]);

            // Create Line Items & Lock Tasks
            foreach ($preview['items'] as $item) {
                // Snapshot in InvoiceLineItem
                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'task_id' => $item['task_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'rate' => $item['rate'],
                    'amount' => $item['amount']
                ]);

                // Lock Task
                Task::where('id', $item['task_id'])->update([
                    'billed_at' => Carbon::now(),
                    'invoice_id' => $invoice->id
                ]);
            }

            return $invoice;
        });
    }
}
