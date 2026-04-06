<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\InvoiceService;
use App\Services\Infrastructure\LoggerService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected $invoiceService;
    protected $logger;

    public function __construct(InvoiceService $invoiceService, LoggerService $logger)
    {
        $this->invoiceService = $invoiceService;
        $this->logger = $logger;
    }

    /**
     * List all Generated Invoices
     */
    public function index(Request $request)
    {
        $query = \App\Models\Invoice::with(['client:id,name', 'project:id,name,code']);
        
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('client', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('project', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        $invoices = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return \Inertia\Inertia::render('Finance/Index', [
            'invoices' => $invoices,
            'filters' => $request->only('search')
        ]);
    }

    /**
     * Get preview of potential invoice items
     */
    public function preview($projectId)
    {
        try {
            $data = $this->invoiceService->preview($projectId);
            return \Inertia\Inertia::render('Finance/InvoicePreview', $data);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->setStatusCode(303);
        }
    }

    /**
     * Generate actual invoice
     */
    public function store($projectId)
    {
        try {
            $invoice = $this->invoiceService->generate($projectId);
            
            $this->logger->log('finance', 'invoice_generated', "Invoice #{$invoice->id} generated for Project #{$projectId} Total: {$invoice->total}");

            return redirect()->back()->with('success', 'Invoice generated successfully.')->setStatusCode(303);
        } catch (\Exception $e) {
            $this->logger->log('finance', 'invoice_error', "Failed to generate invoice for Project #{$projectId}: " . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage())->setStatusCode(303);
        }
    }
}
