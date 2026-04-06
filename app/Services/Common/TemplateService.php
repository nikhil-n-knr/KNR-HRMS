<?php

namespace App\Services\Common;

use App\Models\DocumentTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class TemplateService
{
    /**
     * Render the HTML from the template with data replacement.
     */
    /**
     * Render the HTML from the template with data replacement.
     */
    /**
     * Render the HTML from the template with data replacement.
     * @param string $mode 'pdf' or 'web'
     */
    public function render(DocumentTemplate $template, array $data, string $mode = 'pdf'): string
    {
        // 1. Prepare Content
        $pages = [];
        
        // Handle Multi-page vs Single-page
        if (!empty($template->pages_data) && is_array($template->pages_data)) {
            foreach ($template->pages_data as $page) {
                $pages[] = $this->replaceVariables($page['content'] ?? '', $data);
            }
        } else {
            // Fallback for legacy templates
            $pages[] = $this->replaceVariables($template->body_html, $data);
        }

        // 2. Prepare Layout Elements
        $header = $this->replaceVariables($template->header_html, $data);
        $footer = $this->replaceVariables($template->footer_html, $data);
        
        $layoutConfig = $template->layout_config ?? [];
        $css = $template->css;
        $isWeb = ($mode === 'web');

        // Construct full HTML
        return view('templates.pdf_layout', compact(
            'pages', 
            'header', 
            'footer', 
            'css', 
            'template', 
            'layoutConfig',
            'isWeb'
        ))->render();
    }

    /**
     * Generate PDF binary/object from HTML.
     */
    public function generatePdf(string $html)
    {
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');
        return $pdf;
    }

    /**
     * Generate and store an offer letter PDF.
     */
    public function generateAndStoreOfferPdf(\App\Models\OfferLetter $offer): string
    {
        // Deterministic filename to allow Caching/Reuse
        $filename = 'offer-letters/offer-' . $offer->id . '.pdf';
        
        // Ensure directory exists
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('offer-letters')) {
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('offer-letters');
        }

        // Check if PDF already exists and is up-to-date (optional, based on offer last updated)
        // For now, always regenerate to ensure latest data.
        
        // Render the HTML content for the offer letter
        $html = $this->render($offer->template, $offer->data, 'pdf');

        // Generate the PDF
        $pdf = $this->generatePdf($html);

        // Store the PDF
        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $pdf->output());

        return \Illuminate\Support\Facades\Storage::disk('public')->url($filename);
    }

    /**
     * Simple Variable Replacement {{ candidate.name }}
     */
    protected function replaceVariables(?string $content, array $data): string
    {
        if (empty($content)) return '';

        // 1. Replace Tables First (to ensure they are not broken by other replaces if any overlap)
        $content = $this->replaceTables($content, $data);

        // 2. Flatten data for easier lookup (dot notation)
        $flattened = \Illuminate\Support\Arr::dot($data);

        foreach ($flattened as $key => $value) {
            // Handle only scalar values for replacement to avoid Array-to-String conversions
            if (is_scalar($value)) {
                // Regex to handle variable spacing: {{ key }} or {{key}} or {{  key  }}
                $keyEscaped = preg_quote($key, '/');
                $content = preg_replace('/\{\{\s*' . $keyEscaped . '\s*\}\}/i', $value, $content);
            }
        }
        
        // Handle Signature Image Special Case
        if (isset($data['signature_image']) && !empty($data['signature_image'])) {
            $imgTag = '<img src="' . $data['signature_image'] . '" style="max-height: 80px; width: auto;" alt="Signature" />';
            $content = preg_replace('/\{\{\s*signature\s*\}\}/i', $imgTag, $content);
            // Also support {{ candidate.signature }}
            $content = preg_replace('/\{\{\s*candidate\.signature\s*\}\}/i', $imgTag, $content);
        } else {
            // Remove the tag if no signature
            $content = preg_replace('/\{\{\s*signature\s*\}\}/i', '', $content);
            $content = preg_replace('/\{\{\s*candidate\.signature\s*\}\}/i', '', $content);
        }

        return $content;
    }

    protected function replaceTables(string $content, array $data): string
    {
        // Salary Tables
        if (str_contains($content, '{{ table.salary_simple }}')) {
            $content = str_replace('{{ table.salary_simple }}', $this->generateSalarySimpleTable($data), $content);
        }

        if (str_contains($content, '{{ table.salary_detailed }}')) {
            $content = str_replace('{{ table.salary_detailed }}', $this->generateSalaryDetailedTable($data), $content);
        }

        return $content;
    }

    protected function generateSalarySimpleTable(array $data): string
    {
        // Check if salary data exists
        if (!isset($data['salary'])) return '<p style="color:red">[Salary Data Missing]</p>';
        $salary = $data['salary'];

        // Formatting
        $fmt = fn($val) => number_format((float)$val, 2);

        return '
        <table style="width: 100%; border-collapse: collapse; margin-block: 15px; font-size: 13px; font-family: sans-serif;">
            <thead>
                <tr style="background-color: #f3f4f6;">
                    <th style="border: 1px solid #e5e7eb; padding: 8px; text-align: left;">Description</th>
                    <th style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">Monthly</th>
                    <th style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">Annual</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid #e5e7eb; padding: 8px;"><strong>Total Earnings</strong></td>
                    <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">' . $fmt($salary['gross_salary'] ?? 0) . '</td>
                    <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">' . $fmt(($salary['gross_salary'] ?? 0) * 12) . '</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #e5e7eb; padding: 8px;"><strong>Total Deductions</strong></td>
                    <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">' . $fmt($salary['total_deductions'] ?? 0) . '</td>
                    <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">' . $fmt(($salary['total_deductions'] ?? 0) * 12) . '</td>
                </tr>
                <tr style="background-color: #f9fafb;">
                    <td style="border: 1px solid #e5e7eb; padding: 8px;"><strong>Net Salary</strong></td>
                    <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right; font-weight: bold;">' . $fmt($salary['net_salary'] ?? 0) . '</td>
                    <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right; font-weight: bold;">' . $fmt(($salary['net_salary'] ?? 0) * 12) . '</td>
                </tr>
            </tbody>
        </table>';
    }

    protected function generateSalaryDetailedTable(array $data): string
    {
        if (!isset($data['salary'])) return '<p style="color:red">[Salary Data Missing]</p>';
        $salary = $data['salary'];
        $earnings = $salary['earnings'] ?? []; // List of components
        $deductions = $salary['deductions'] ?? []; // List of components
        
        // Mocking structure if simple array (needs robust structure from CalculatorService)
        // Assuming $earnings = [['name' => 'Basic', 'amount' => 5000], ...]

        $html = '
        <table style="width: 100%; border-collapse: collapse; margin-block: 15px; font-size: 13px; font-family: sans-serif;">
            <thead>
                <tr style="background-color: #e5e7eb;">
                    <th style="border: 1px solid #9ca3af; padding: 8px; text-align: left;">Component</th>
                    <th style="border: 1px solid #9ca3af; padding: 8px; text-align: right;">Monthly</th>
                    <th style="border: 1px solid #9ca3af; padding: 8px; text-align: right;">Annual</th>
                </tr>
            </thead>
            <tbody>';
        
        $fmt = fn($val) => number_format((float)$val, 2);
        
        // Earnings Section
        $html .= '<tr><td colspan="3" style="border: 1px solid #e5e7eb; padding: 6px; background: #f3f4f6; font-weight: bold; font-size: 12px;">Earnings</td></tr>';
        
        foreach ($earnings as $name => $amount) {
            // Handle key-value pair or object
            $label = is_string($name) ? $name : ($amount['name'] ?? 'Allowance');
            $val = is_numeric($amount) ? $amount : ($amount['amount'] ?? 0);
            
             $html .= '
                <tr>
                    <td style="border: 1px solid #e5e7eb; padding: 8px;">' . ucwords(str_replace('_', ' ', $label)) . '</td>
                    <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">' . $fmt($val) . '</td>
                    <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">' . $fmt($val * 12) . '</td>
                </tr>';
        }

        // Deductions Section
        $html .= '<tr><td colspan="3" style="border: 1px solid #e5e7eb; padding: 6px; background: #f3f4f6; font-weight: bold; font-size: 12px;">Deductions</td></tr>';

        foreach ($deductions as $name => $amount) {
             $label = is_string($name) ? $name : ($amount['name'] ?? 'Deduction');
             $val = is_numeric($amount) ? $amount : ($amount['amount'] ?? 0);
             
             $html .= '
                <tr>
                    <td style="border: 1px solid #e5e7eb; padding: 8px;">' . ucwords(str_replace('_', ' ', $label)) . '</td>
                    <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">' . $fmt($val) . '</td>
                    <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">' . $fmt($val * 12) . '</td>
                </tr>';
        }
        
        // Footer (Net)
         $html .= '
                <tr style="background-color: #374151; color: white;">
                    <td style="border: 1px solid #374151; padding: 8px;"><strong>Net Salary Payable</strong></td>
                    <td style="border: 1px solid #374151; padding: 8px; text-align: right; font-weight: bold;">' . $fmt($salary['net_salary'] ?? 0) . '</td>
                    <td style="border: 1px solid #374151; padding: 8px; text-align: right; font-weight: bold;">' . $fmt(($salary['net_salary'] ?? 0) * 12) . '</td>
                </tr>
            </tbody>
        </table>';

        return $html;
    }
}
