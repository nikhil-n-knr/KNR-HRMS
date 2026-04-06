<?php

namespace App\Http\Controllers\LMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StakeholderController extends Controller
{
    /**
     * Parent/Guardian Hub: View ward's progress.
     */
    public function parentHub()
    {
        return Inertia::render('LMS/Stakeholder/ParentHub', [
            'ward' => [
                'name' => 'Aryan Soni',
                'grade' => 'A+',
                'overall_progress' => 88
            ],
            'courses' => [
                ['id' => 1, 'title' => 'Advanced EV Powertrains', 'progress' => 92, 'status' => 'Excellent'],
                ['id' => 2, 'title' => 'Battery Management', 'progress' => 45, 'status' => 'Needs Focus']
            ]
        ]);
    }

    /**
     * Employer Transcript: Verify employee certifications.
     */
    public function employerTranscript($employeeId)
    {
        return Inertia::render('LMS/Stakeholder/EmployerTranscript', [
            'employee' => [
                'name' => 'Nikhil Soni',
                'id' => $employeeId,
                'department' => 'Engineering'
            ],
            'certificates' => [
                ['id' => 'CERT-101', 'title' => 'Hydrogen Fuel Cell Specialist', 'issued_at' => '2026-01-15', 'valid_until' => '2028-01-15'],
                ['id' => 'CERT-202', 'title' => 'BMS Layer 1 Architect', 'issued_at' => '2025-11-20', 'valid_until' => '2027-11-20']
            ]
        ]);
    }
}
