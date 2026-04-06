<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\LmsCourse;
use App\Models\LmsCourseContent;
use App\Models\LmsQuestion;
use App\Models\LmsAssignment;
use App\Models\LmsAttempt;
use App\Models\LmsCertificate;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LmsGoldenDataSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('email', 'admin@test.com')->first();
        if (!$admin) return;

        // 1. Create Courses
        $courses = [
            [
                'title' => 'PoSH Training 2026',
                'category' => 'Compliance',
                'description' => 'Mandatory Prevention of Sexual Harassment training for all employees.',
                'validity_days' => 365,
                'passing_score' => 80,
                'max_attempts' => 3,
                'questions' => [
                    [
                        'text' => 'Which of the following constitutes sexual harassment under the POSH Act?',
                        'options' => [
                            ['id' => 'q1o1', 'text' => 'Unwelcome sexual advances', 'is_correct' => true],
                            ['id' => 'q1o2', 'text' => 'Asking for a work-related favor', 'is_correct' => false],
                            ['id' => 'q1o3', 'text' => 'Disagreeing with a colleague on a project', 'is_correct' => false],
                            ['id' => 'q1o4', 'text' => 'Giving constructive feedback on performance', 'is_correct' => false],
                        ]
                    ],
                    [
                        'text' => 'Who is an "Aggrieved Woman" under the POSH Act?',
                        'options' => [
                            ['id' => 'q2o1', 'text' => 'Only full-time female employees', 'is_correct' => false],
                            ['id' => 'q2o2', 'text' => 'Any woman, regardless of age or employment status, who alleges sexual harassment', 'is_correct' => true],
                            ['id' => 'q2o3', 'text' => 'Only women working in the private sector', 'is_correct' => false],
                            ['id' => 'q2o4', 'text' => 'Only women who have a written contract', 'is_correct' => false],
                        ]
                    ]
                ]
            ],
            [
                'title' => 'AWS Security Essentials',
                'category' => 'Engineering',
                'description' => 'Fundamental security concepts and services in Amazon Web Services.',
                'validity_days' => 730,
                'passing_score' => 75,
                'max_attempts' => 5,
                'questions' => [
                    [
                        'text' => 'Which AWS service is used for protecting against DDoS attacks?',
                        'options' => [
                            ['id' => 'a1o1', 'text' => 'AWS Shield', 'is_correct' => true],
                            ['id' => 'a1o2', 'text' => 'Amazon Inspector', 'is_correct' => false],
                            ['id' => 'a1o3', 'text' => 'AWS WAF', 'is_correct' => false],
                            ['id' => 'a1o4', 'text' => 'Amazon GuardDuty', 'is_correct' => false],
                        ]
                    ],
                    [
                        'text' => 'What is the primary purpose of IAM?',
                        'options' => [
                            ['id' => 'a2o1', 'text' => 'To manage access to AWS services and resources securely', 'is_correct' => true],
                            ['id' => 'a2o2', 'text' => 'To encrypt data at rest', 'is_correct' => false],
                            ['id' => 'a2o3', 'text' => 'To monitor network traffic', 'is_correct' => false],
                            ['id' => 'a2o4', 'text' => 'To automate server deployments', 'is_correct' => false],
                        ]
                    ]
                ]
            ],
            [
                'title' => 'Strategic Interviewing',
                'category' => 'Soft Skills',
                'description' => 'Learn how to conduct effective, unbiased, and strategic interviews.',
                'validity_days' => 1095,
                'passing_score' => 70,
                'max_attempts' => 2,
                'questions' => [
                    [
                        'text' => 'What is a "Behavioral Interview Question"?',
                        'options' => [
                            ['id' => 's1o1', 'text' => 'A question about future hypothetical situations', 'is_correct' => false],
                            ['id' => 's1o2', 'text' => 'A question that asks candidates to describe past work experiences and actions', 'is_correct' => true],
                            ['id' => 's1o3', 'text' => 'A question about a candidate\'s hobbies', 'is_correct' => false],
                            ['id' => 's1o4', 'text' => 'A question testing technical coding skills', 'is_correct' => false],
                        ]
                    ]
                ]
            ]
        ];

        $createdCourses = [];

        foreach ($courses as $c) {
            $course = LmsCourse::updateOrCreate(
                ['slug' => Str::slug($c['title'])],
                [
                    'title' => $c['title'],
                    'description' => $c['description'],
                    'category' => $c['category'],
                    'validity_days' => $c['validity_days'],
                    'passing_score' => $c['passing_score'],
                    'max_attempts' => $c['max_attempts'],
                    'is_active' => true,
                    'created_by' => $admin->id
                ]
            );

            // Add Content Section
            LmsCourseContent::updateOrCreate(
                ['course_id' => $course->id, 'title' => 'Introduction'],
                [
                    'type' => 'text',
                    'content' => 'Welcome to ' . $c['title'] . '. Please read through the materials before taking the quiz.',
                    'order' => 1
                ]
            );

            // Add Questions
            foreach ($c['questions'] as $qData) {
                LmsQuestion::updateOrCreate(
                    ['course_id' => $course->id, 'question_text' => $qData['text']],
                    [
                        'type' => 'mcq',
                        'options' => $qData['options'],
                        'max_score' => 10,
                        'score_weight' => 1,
                        'is_active' => true
                    ]
                );
            }

            $createdCourses[$c['title']] = $course;
        }

        // 2. Map Attempts for our "Golden 5"
        $employees = Employee::whereIn('email', [
            'vikram@golden.test', 'anjali@golden.test', 'rahul@golden.test', 
            'sneha@golden.test', 'amit@golden.test'
        ])->get();

        foreach ($employees as $emp) {
            // Everyone gets PoSH
            $this->assignAndAttempt($emp, $createdCourses['PoSH Training 2026'], $admin, true);

            // Techies get AWS
            if (in_array($emp->email, ['vikram@golden.test', 'rahul@golden.test', 'sneha@golden.test'])) {
                if ($emp->email === 'sneha@golden.test') {
                    $this->assignAndAttempt($emp, $createdCourses['AWS Security Essentials'], $admin, false); // Pending
                } else {
                    $this->assignAndAttempt($emp, $createdCourses['AWS Security Essentials'], $admin, true);
                }
            }

            // HR gets Strategic Interviewing
            if (in_array($emp->email, ['anjali@golden.test', 'amit@golden.test'])) {
                if ($emp->email === 'anjali@golden.test') {
                    // Anjali failed first attempt, currently ongoing
                    $this->assignAndAttempt($emp, $createdCourses['Strategic Interviewing'], $admin, 'fail');
                } else {
                    $this->assignAndAttempt($emp, $createdCourses['Strategic Interviewing'], $admin, true);
                }
            }
        }

        $this->command->info('LMS Golden Data Seeded Successfully!');
    }

    private function assignAndAttempt($emp, $course, $admin, $outcome)
    {
        $status = $outcome === false ? 'pending' : ($outcome === 'fail' ? 'in_progress' : 'completed');
        
        $assignment = LmsAssignment::updateOrCreate(
            ['course_id' => $course->id, 'employee_id' => $emp->id],
            [
                'assigned_on' => Carbon::now()->subMonths(1),
                'due_date' => Carbon::now()->addMonths(1),
                'status' => $status,
                'assigned_by' => $admin->id,
                'completed_at' => $outcome === true ? Carbon::now()->subDays(5) : null
            ]
        );

        if ($outcome === true) {
            // Build simple answers_log
            $answersLog = [];
            foreach ($course->questions as $q) {
                $correct = collect($q->options)->firstWhere('is_correct', true);
                $answersLog[$q->id] = ['selected' => $correct['id']];
            }

            $attempt = LmsAttempt::create([
                'assignment_id' => $assignment->id,
                'employee_id' => $emp->id,
                'course_id' => $course->id,
                'attempt_number' => 1,
                'started_at' => Carbon::now()->subDays(5)->subMinutes(30),
                'submitted_at' => Carbon::now()->subDays(5),
                'score_obtained' => count($answersLog) * 10,
                'max_score' => count($answersLog) * 10,
                'percentage' => 100,
                'is_passed' => true,
                'status' => 'submitted',
                'answers_log' => $answersLog,
                'violations' => []
            ]);

            LmsCertificate::updateOrCreate(
                ['employee_id' => $emp->id, 'course_id' => $course->id],
                [
                    'attempt_id' => $attempt->id,
                    'certificate_code' => 'CERT-' . strtoupper(Str::random(8)),
                    'issued_on' => Carbon::now()->subDays(5),
                    'pdf_path' => 'certificates/dummy.pdf',
                    'qr_code_path' => 'certificates/qr_dummy.png'
                ]
            );
        } elseif ($outcome === 'fail') {
            $answersLog = [];
            foreach ($course->questions as $q) {
                // Find a wrong option
                $wrong = collect($q->options)->firstWhere('is_correct', false);
                $answersLog[$q->id] = ['selected' => $wrong['id'] ?? 'none'];
            }

            LmsAttempt::create([
                'assignment_id' => $assignment->id,
                'employee_id' => $emp->id,
                'course_id' => $course->id,
                'attempt_number' => 1,
                'started_at' => Carbon::now()->subDays(2),
                'submitted_at' => Carbon::now()->subDays(2)->addMinutes(10),
                'score_obtained' => 0,
                'max_score' => count($answersLog) * 10,
                'percentage' => 0,
                'is_passed' => false,
                'status' => 'submitted',
                'answers_log' => $answersLog,
                'violations' => []
            ]);
        }
    }
}
