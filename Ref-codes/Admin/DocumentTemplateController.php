<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\DocumentTemplate;
use Inertia\Inertia;
use App\Services\Common\TemplateService;

class DocumentTemplateController extends Controller
{
    protected $templateService;

    public function __construct(TemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    public function previewPdf(DocumentTemplate $documentTemplate)
    {
        set_time_limit(300); // Allow 5 minutes for PDF generation
        ini_set('memory_limit', '512M'); // Increase memory limit

        $mockData = [
            'candidate' => [
                'id' => 'EMP001', 
                'name' => 'John Doe', 
                'email' => 'john.doe@example.com', 
                'job_title' => 'Senior Software Engineer', 
                'joining_date' => now()->addDays(15)->format('d M Y'),
                'department' => 'Engineering'
            ],
            'salary' => [
                'ctc' => '₹12,00,000', 
                'gross_salary' => '₹1,00,000', 
                'net_salary' => '₹92,000'
            ],
            'company' => [
                'name' => 'Acme Corp Solutions', 
                'address' => 'Tech Park, Bangalore'
            ],
            'date' => now()->format('d M Y'),
            'year' => now()->format('Y')
        ];

        $html = $this->templateService->render($documentTemplate, $mockData);
        $pdf = $this->templateService->generatePdf($html);
        
        return $pdf->stream('preview-' . $documentTemplate->id . '.pdf');
    }

    public function index()
    {
        return Inertia::render('Admin/DocumentTemplates/Index', [
            'templates' => DocumentTemplate::latest()->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/DocumentTemplates/Edit', [
            'template' => new DocumentTemplate(),
            'variables' => [
                'Candidate' => ['candidate.name', 'candidate.email', 'candidate.job_title', 'candidate.joining_date'],
                'Salary' => ['salary.ctc', 'salary.gross_salary', 'salary.net_salary'],
                'Company' => ['company.name', 'company.address'],
                'Data Tables' => ['table.salary_simple', 'table.salary_detailed']
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:offer,appointment,appraisal,promotion,warning,relieving,payslip,policy,letter',
            'body_html' => 'nullable', // Nullable if pages_data is present
            'header_html' => 'nullable',
            'footer_html' => 'nullable',
            'watermark_text' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,docx,doc|max:5120', // 5MB max
        ]);

        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Store original name for display
                $path = $file->store('template-attachments', 'public');
                $attachmentPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path
                ];
            }
        }

        // Handle Image Uploads or Library Paths
        $headerPath = null;
        if ($request->hasFile('header_image')) {
            $headerPath = $request->file('header_image')->store('templates/headers', 'public');
        } elseif ($request->header_image_path) {
            $headerPath = $request->header_image_path; // Use library path
        }

        $footerPath = null;
        if ($request->hasFile('footer_image')) {
            $footerPath = $request->file('footer_image')->store('templates/footers', 'public');
        } elseif ($request->footer_image_path) {
            $footerPath = $request->footer_image_path;
        }

        $watermarkPath = null;
        if ($request->hasFile('watermark_image')) {
            $watermarkPath = $request->file('watermark_image')->store('templates/watermarks', 'public');
        }
        
        // Handle Layout Config parsing if sent as string
        $layoutConfig = $request->input('layout_config');
        if (is_string($layoutConfig)) {
            $layoutConfig = json_decode($layoutConfig, true);
        }

        // Handle Pages Data parsing if sent as string
        $pagesData = $request->input('pages_data');
        if (is_string($pagesData)) {
            $pagesData = json_decode($pagesData, true);
        }

        DocumentTemplate::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'body_html' => $validated['body_html'] ?? '', // Fallback for single page
            'header_html' => $validated['header_html'],
            'footer_html' => $validated['footer_html'],
            'watermark_text' => $validated['watermark_text'],
            'is_active' => $request->boolean('is_active'),
            'attachments' => $attachmentPaths,
            // New Fields
            'layout_config' => $layoutConfig,
            'pages_data' => $pagesData,
            'header_image' => $headerPath,
            'footer_image' => $footerPath,
            'watermark_image' => $watermarkPath,
        ]);

        return to_route('admin.document-templates.index')
            ->with('success', 'Document Template created successfully.');
    }

    public function show(DocumentTemplate $documentTemplate)
    {
        //
    }

    public function edit(DocumentTemplate $documentTemplate)
    {
        // Mock variables for different types
        $variables = [
            'candidate' => ['candidate.name', 'candidate.email', 'candidate.job_title', 'candidate.joining_date', 'candidate.id'],
            'salary' => ['salary.ctc', 'salary.gross_salary', 'salary.net_salary'],
            'company' => ['company.name', 'company.address'],
            'general' => ['date', 'year']
        ];

        return Inertia::render('Admin/DocumentTemplates/Edit', [
            'template' => $documentTemplate,
            'variables' => $variables
        ]);
    }

    public function update(Request $request, DocumentTemplate $documentTemplate)
    {
        set_time_limit(300); // Allow 5 minutes for uploads/processing
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:offer,appointment,appraisal,promotion,warning,relieving,payslip,policy,letter',
            'body_html' => 'nullable', // Nullable if pages_data is present
            'header_html' => 'nullable',
            'footer_html' => 'nullable',
            'watermark_text' => 'nullable',
            'existing_attachments' => 'nullable|array',
            'new_attachments.*' => 'nullable|file|mimes:pdf,docx,doc|max:5120',
            'header_image' => 'nullable', // file or string path
            'footer_image' => 'nullable', // file or string path
            'watermark_image' => 'nullable|image|max:2048', // 2MB max
            'layout_config' => 'nullable',
            'pages_data' => 'nullable',
            'is_active' => 'boolean',
        ]);

        // Merge Existing
        $currentAttachments = $documentTemplate->attachments ?? [];
        if ($request->has('existing_attachments')) {
             $keptAttachments = [];
             foreach($request->input('existing_attachments') as $existing) {
                 // Verify integrity if needed, keeping simple for now
                 $keptAttachments[] = $existing;
             }
             $currentAttachments = $keptAttachments;
        }

        if ($request->hasFile('new_attachments')) {
            foreach ($request->file('new_attachments') as $file) {
                $path = $file->store('template-attachments', 'public');
                $currentAttachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'url' => \Illuminate\Support\Facades\Storage::url($path)
                ];
            }
        }

        // Handle Image Uploads - Explicitly check and store
        if ($request->hasFile('header_image')) {
            $path = $request->file('header_image')->store('templates/headers', 'public');
            $validated['header_image'] = $path;
        } elseif ($request->header_image_path) {
            $validated['header_image'] = $request->header_image_path;
        }

        if ($request->hasFile('footer_image')) {
            $path = $request->file('footer_image')->store('templates/footers', 'public');
            $validated['footer_image'] = $path;
        } elseif ($request->footer_image_path) {
            $validated['footer_image'] = $request->footer_image_path;
        }

        if ($request->hasFile('watermark_image')) {
            $path = $request->file('watermark_image')->store('templates/watermarks', 'public');
            $validated['watermark_image'] = $path;
        }

        $validated['attachments'] = $currentAttachments;
        unset($validated['new_attachments']);
        unset($validated['existing_attachments']);

        // Handle Layout Config parsing
        if ($request->has('layout_config')) {
             $config = $request->input('layout_config');
             if (is_string($config)) {
                 $config = json_decode($config, true);
             }
             $validated['layout_config'] = $config;
        }
        
        // Handle Pages Data parsing
        if ($request->has('pages_data')) {
             $pages = $request->input('pages_data');
             if (is_string($pages)) {
                 $pages = json_decode($pages, true);
             }
             $validated['pages_data'] = $pages;
        }

        // Force update of attributes to ensure dirty state check doesn't skip
        $documentTemplate->fill($validated);
        $documentTemplate->save();

         return to_route('admin.document-templates.index')->with('success', 'Template Updated');
    }

    public function destroy(DocumentTemplate $document_template)
    {
        $document_template->delete();
        return back()->with('success', 'Template Deleted');
    }

    // Component Library Methods
    public function saveComponent(Request $request) {
        $data = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:header,footer',
            'content' => 'nullable',
            'settings' => 'nullable', // Allow JSON string
            'is_default' => 'boolean',
            'image_file' => 'nullable|image|max:2048' // Increased limit slightly
        ]);

        if (isset($data['settings']) && is_string($data['settings'])) {
            $data['settings'] = json_decode($data['settings'], true);
        }

        $imagePath = null;
        if ($request->hasFile('image_file')) {
            $imagePath = $request->file('image_file')->store('components/' . $data['type'] . 's', 'public');
        }

        \App\Models\DocumentComponent::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'content' => $data['content'],
            'settings' => $data['settings'],
            'image_path' => $imagePath,
            'is_default' => $request->boolean('is_default')
        ]);

        return back()->with('success', 'Component saved to library successfully.');
    }

    public function fetchComponents(Request $request) {
        $type = $request->query('type');
        $query = \App\Models\DocumentComponent::where('is_active', true);
        
        if ($type) {
            $query->where('type', $type);
        }

        return response()->json($query->orderBy('is_default', 'desc')->latest()->get());
    }
}
