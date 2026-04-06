<?php

namespace App\Http\Controllers\HR\LMS;

use App\Http\Controllers\Controller;
use App\Models\LmsCertificate;
use App\Services\LMS\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function __construct(
        private CertificateService $certificateService
    ) {}
    
    /**
     * Download certificate PDF
     */
    public function download(LmsCertificate $certificate)
    {
        // Verify ownership or admin
        if (auth()->user()->employee_id !== $certificate->employee_id && 
            !auth()->user()->hasRole(['Admin', 'HR'])) {
            abort(403);
        }
        
        if ($certificate->isExpired()) {
            return back()->with('error', 'This certificate has expired');
        }
        
        $certificate->incrementDownloads();
        
        return Storage::disk('public')->download(
            $certificate->pdf_path,
            "Certificate_{$certificate->certificate_code}.pdf"
        );
    }
    
    /**
     * Verify certificate by code
     */
    public function verify(string $code)
    {
        $result = $this->certificateService->verify($code);
        
        if (!$result) {
            return response()->json([
                'valid' => false,
                'message' => 'Certificate not found'
            ], 404);
        }
        
        return response()->json($result);
    }
}
