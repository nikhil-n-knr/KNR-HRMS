<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\LmsCourse;
use App\Models\LMS\LmsCategory;
use App\Models\LMS\LmsInstitution;
use App\Models\LMS\LmsModule;
use App\Models\LMS\LmsChapter;
use App\Models\LMS\LmsConcept;
use App\Models\LMS\LmsActivity;
use App\Models\LMS\LmsVideoLesson;
use App\Models\LMS\LmsQuizConfig;
use App\Models\LMS\LmsEnrollment;
use App\Models\LMS\LmsCourseProgress;
use App\Models\User;
use Illuminate\Support\Str;

class LmsComprehensiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Prevent event triggers from slowing it down
        LmsCourse::flushEventListeners();
        LmsModule::flushEventListeners();

        $this->command->info('Starting Comprehensive LMS Data Generation...');

        // 1. Categories
        $categories = ['Engineering & Tech', 'Business & Leadership', 'Design & UX', 'Personal Development'];
        $catIds = [];
        foreach ($categories as $cat) {
            $catIds[] = LmsCategory::updateOrCreate(['name' => $cat], [
                'name' => $cat,
                'slug' => Str::slug($cat),
                'is_active' => true,
            ])->id;
        }
        $this->command->info('Categories seeded.');

        // 2. Institutions (optional, default to HRMS internal)
        $institution = LmsInstitution::firstOrCreate(
            ['name' => 'HRMS Global Academy'],
            ['code' => 'HRMS-GLOB', 'type' => 'university', 'is_active' => true]
        );

        // Fetch instructors and students
        $users = User::paginate(200);
        if ($users->count() === 0) {
            $this->command->error('No users found. Run basic HRMS seeder first.');
            return;
        }
        $instructors = $users->take(5)->pluck('id')->toArray();
        $students = $users->skip(5)->pluck('id')->toArray();

        // 3. Courses (50+)
        $this->command->info('Seeding 55 Courses...');
        DB::beginTransaction();
        try {
            $courses = [];
            for ($i = 1; $i <= 55; $i++) {
                $category = $catIds[array_rand($catIds)];
                $creator = $instructors[array_rand($instructors)];
                
                $course = LmsCourse::create([
                    'title' => "Advanced Professional Certification " . $this->generateTitleSuffix($i),
                    'slug' => Str::slug("Advanced Professional Certification " . $i . " " . Str::random(5)),
                    'description' => "This is a comprehensive, deep-dive certification designed to catapult your professional skills. It encompasses rigorous theory and practical evaluation.",
                    'category_id' => $category,
                    'institution_id' => $institution->id,
                    'created_by' => $creator,
                    'is_published' => true,
                    'is_active' => true,
                    'level' => ['beginner', 'intermediate', 'advanced'][array_rand(['beginner', 'intermediate', 'advanced'])],
                    'mode' => 'standalone',
                    'allow_self_enrollment' => true,
                    'thumbnail_path' => null, // Provide a default UI gradient in frontend if null
                ]);
                
                $courses[] = $course;

                // 4. Modules per Course (3 to 6)
                $moduleCount = rand(3, 6);
                for ($m = 1; $m <= $moduleCount; $m++) {
                    $module = $course->modules()->create([
                        'title' => "Module $m: " . $this->generateModuleTitle($m),
                        'sort_order' => $m,
                        'is_mandatory' => true,
                        'is_active' => true,
                    ]);

                    // 5. Chapters per Module (2 to 4)
                    $chapterCount = rand(2, 4);
                    for ($c = 1; $c <= $chapterCount; $c++) {
                        $chapter = $module->chapters()->create([
                            'course_id' => $course->id,
                            'title' => "Chapter $c: Fundamentals",
                            'sort_order' => $c,
                            'is_mandatory' => true,
                            'is_active' => true,
                        ]);

                        // 6. Concepts (Topics) per Chapter (3 to 5) => Total ~1000 topics
                        $conceptCount = rand(3, 5);
                        for ($con = 1; $con <= $conceptCount; $con++) {
                            $concept = $chapter->concepts()->create([
                                'course_id' => $course->id,
                                'module_id' => $module->id,
                                'title' => "Topic: " . $this->generateConceptTitle(),
                                'sort_order' => $con,
                                'is_mandatory' => true,
                                'is_active' => true,
                                'estimated_duration_minutes' => rand(15, 60),
                            ]);

                            // 7. Activities (Tasks) per Concept (1 to 2) => Total ~1500 tasks
                            $activityCount = rand(1, 2);
                            for ($a = 1; $a <= $activityCount; $a++) {
                                $type = $a === 1 ? 'video' : 'quiz';
                                
                                $activity = $concept->activities()->create([
                                    'course_id' => $course->id,
                                    'module_id' => $module->id,
                                    'chapter_id' => $chapter->id,
                                    'title' => $type === 'video' ? 'Interactive Video Lesson' : 'Knowledge Check',
                                    'type' => $type,
                                    'sort_order' => $a,
                                    'is_mandatory' => true,
                                    'is_graded' => $type === 'quiz',
                                    'is_active' => true,
                                ]);

                                if ($type === 'video') {
                                    LmsVideoLesson::create([
                                        'activity_id' => $activity->id,
                                        'source_type' => 'url',
                                        // A valid placeholder video
                                        'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                                        'duration_seconds' => rand(300, 1200),
                                        'min_watch_pct' => 90,
                                        'disable_seeking' => true,
                                    ]);
                                } else {
                                    LmsQuizConfig::create([
                                        'activity_id' => $activity->id,
                                        'title' => 'Knowledge Check',
                                        'pass_mark_pct' => 80,
                                        'duration_minutes' => 15,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // 8. Learn Population & Enrollments
            $this->command->info('Enrolling learners and generating progress... this might take a moment.');
            if (count($students) > 0) {
                foreach ($courses as $cIndex => $course) {
                    // Pick 5 to 20 random students per course
                    shuffle($students);
                    $enrollmentCount = min(rand(5, 20), count($students));
                    $enrolledStudents = array_slice($students, 0, $enrollmentCount);

                    foreach ($enrolledStudents as $sId) {
                        $enrollment = LmsEnrollment::create([
                            'user_id' => $sId,
                            'course_id' => $course->id,
                            'institution_id' => $institution->id,
                            'status' => 'active',
                            'source' => 'bulk_import',
                        ]);

                        // Generate random progress
                        $isCompleted = rand(0, 1) == 1;
                        $pct = $isCompleted ? 100 : rand(10, 80);
                        LmsCourseProgress::updateOrCreate(
                            ['user_id' => $sId, 'course_id' => $course->id],
                            [
                                'enrollment_id' => $enrollment->id,
                                'completion_pct' => $pct,
                                'is_completed' => $isCompleted,
                                'completed_at' => $isCompleted ? now()->subDays(rand(1, 30)) : null,
                                'total_watch_seconds' => rand(3600, 14400),
                                'avg_quiz_score' => $isCompleted ? rand(80, 100) : rand(40, 70),
                            ]
                        );
                    }
                    
                    // Update cache for course
                    $course->update(['enrolled_count' => $enrollmentCount]);
                }
            }

            DB::commit();
            $this->command->info('Ecosystem Successfully Scaffoled!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding data: ' . $e->getMessage());
        }
    }

    private function generateTitleSuffix($i)
    {
        $suffixes = ['in AI Implementation', 'for Project Managers', 'in Ethical Hacking', 'in Cloud Architecture', 'for Financial Analysts', 'in Creative Design', 'in DevOps Engineering'];
        return $suffixes[$i % count($suffixes)] . " Track " . rand(100, 999);
    }

    private function generateModuleTitle($i)
    {
        $titles = ['Core Foundations', 'Advanced Implementation', 'Strategic Analysis', 'Operational Risk Integration'];
        return $titles[$i % count($titles)];
    }

    private function generateConceptTitle()
    {
        $concepts = [
            'Understanding the Lifecycle', 'Metric-Driven Analysis', 'Data Security Protocols', 
            'Agile Deployment Strategies', 'Cross-functional Synergy', 'Automated Thresholds'
        ];
        return $concepts[array_rand($concepts)] . ' - ' . Str::random(3);
    }
}
