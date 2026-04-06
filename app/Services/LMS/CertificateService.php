<?php

namespace App\Services\LMS;

use App\Models\LmsCertificate;
use App\Models\LmsAttempt;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CertificateService
{
    /**
     * Generate certificate for passed attempt
     */
    public function generate(LmsAttempt $attempt): LmsCertificate
    {
        if (!$attempt->is_passed) {
            throw new \Exception('Cannot generate certificate for failed attempt');
        }
        
        // Check if certificate already exists
        $existing = LmsCertificate::where('attempt_id', $attempt->id)->first();
        if ($existing) {
            return $existing;
        }
        
        $employee = $attempt->employee;
        $course = $attempt->course;
        
        // Generate unique code
        $code = 'CERT-' . strtoupper(Str::random(10));
        
        Log::info('Generating certificate', [
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'code' => $code
        ]);
        
        // Create certificate record
        $certificate = LmsCertificate::create([
            'employee_id' => $employee->id,
            'course_id' => $course->id,
            'attempt_id' => $attempt->id,
            'certificate_code' => $code,
            'issued_on' => now(),
            'expires_on' => $course->validity_days ? now()->addDays($course->validity_days) : null,
            'pdf_path' => '', // Will update after generation
            'qr_code_path' => ''
        ]);
        
        // Generate QR Code
        $verifyUrl = route('hr.lms.verify', $code);
        $qrPath = "certificates/qr/{$code}.png";
        
        QrCode::format('png')
            ->size(200)
            ->generate($verifyUrl, storage_path("app/public/{$qrPath}"));
        
        // Generate PDF
        $pdfPath = "certificates/pdf/{$code}.pdf";
        
        $pdf = Pdf::loadView('lms.certificate', [
            'employee' => $employee,
            'course' => $course,
            'certificate' => $certificate,
            'attempt' => $attempt,
            'qr_url' => asset("storage/{$qrPath}")
        ]);
        
        Storage::put("public/{$pdfPath}", $pdf->output());
        
        // Update paths
        $certificate->update([
            'pdf_path' => $pdfPath,
            'qr_code_path' => $qrPath
        ]);
        
        Log::info('Certificate generated successfully', ['certificate_id' => $certificate->id]);
        
        return $certificate;
    }
    
    /**
     * Verify certificate by code
     */
    public function verify(string $code): ?array
    {
        $certificate = LmsCertificate::with(['employee', 'course', 'attempt'])
            ->where('certificate_code', $code)
            ->first();
            
        if (!$certificate) {
            return null;
        }
        
        return [
            'valid' => !$certificate->isExpired(),
            'employee_name' => $certificate->employee->full_name,
            'course_title' => $certificate->course->title,
            'issued_on' => $certificate->issued_on->format('M d, Y'),
            'expires_on' => $certificate->expires_on?->format('M d, Y'),
            'score' => $certificate->attempt->percentage . '%',
            'certificate_code' => $certificate->certificate_code
        ];
    }
    
    /**
     * Bulk generate certificates for all passed attempts
     */
    public function bulkGenerate(int $courseId): array
    {
        $passedAttempts = LmsAttempt::where('course_id', $courseId)
            ->where('is_passed', true)
            ->whereDoesntHave('certificate')
            ->get();
            
        $generated = 0;
        $errors = [];
        
        foreach ($passedAttempts as $attempt) {
            try {
                $this->generate($attempt);
                $generated++;
            } catch (\Exception $e) {
                $errors[] = "Attempt {$attempt->id}: {$e->getMessage()}";
                Log::error('Certificate generation failed', [
                    'attempt_id' => $attempt->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return [
            'generated' => $generated,
            'errors' => $errors
        ];
    }
}
