<?php

namespace App\Services\LMS;

use App\Models\LmsCourse;
use App\Models\LmsQuestion;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class QuestionImportService
{
    /**
     * Import questions from CSV file
     */
    public function importFromCsv($file, int $courseId): array
    {
        $course = LmsCourse::findOrFail($courseId);
        
        $results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        try {
            $handle = fopen($file->getRealPath(), 'r');
            
            // Read header
            $header = fgetcsv($handle);
            
            if (!$this->validateHeader($header)) {
                throw new \Exception('Invalid CSV header. Expected: type,question_text,option_1,option_2,option_3,option_4,correct_answer,max_score,difficulty');
            }
            
            $rowNumber = 1;
            
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $results['total']++;
                
                try {
                    $questionData = $this->parseRow($header, $row);
                    $this->createQuestion($course, $questionData);
                    $results['success']++;
                    
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                        'data' => $row
                    ];
                }
            }
            
            fclose($handle);
            
            Log::info('Question import completed', [
                'course_id' => $courseId,
                'total' => $results['total'],
                'success' => $results['success'],
                'failed' => $results['failed']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Question import failed', [
                'course_id' => $courseId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
        
        return $results;
    }
    
    /**
     * Validate CSV header
     */
    private function validateHeader(array $header): bool
    {
        $required = ['type', 'question_text', 'option_1', 'option_2', 'correct_answer', 'max_score'];
        
        foreach ($required as $field) {
            if (!in_array($field, $header)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Parse CSV row into question data
     */
    private function parseRow(array $header, array $row): array
    {
        $data = array_combine($header, $row);
        
        // Build options array
        $options = [];
        $correctIndex = (int)$data['correct_answer'];
        
        for ($i = 1; $i <= 10; $i++) {
            $key = "option_{$i}";
            if (isset($data[$key]) && !empty($data[$key])) {
                $options[] = [
                    'id' => $i,
                    'text' => $this->sanitize($data[$key]),
                    'is_correct' => $i === $correctIndex,
                    'score' => $i === $correctIndex ? (float)$data['max_score'] : 0
                ];
            }
        }
        
        if (count($options) < 2 && in_array($data['type'], ['mcq', 'multi_select'])) {
            throw new \Exception('At least 2 options required for MCQ/multi-select questions');
        }
        
        return [
            'type' => $data['type'],
            'question_text' => $this->sanitize($data['question_text']),
            'options' => $options,
            'max_score' => (float)$data['max_score'],
            'difficulty' => $data['difficulty'] ?? 'medium',
            'scenario_context' => $data['scenario_context'] ?? null,
            'image_url' => $data['image_url'] ?? null
        ];
    }
    
    /**
     * Create question in database
     */
    private function createQuestion(LmsCourse $course, array $data): LmsQuestion
    {
        $validator = Validator::make($data, [
            'type' => 'required|in:mcq,multi_select,text,scenario,image_based',
            'question_text' => 'required|string|max:1000',
            'options' => 'required|array|min:2',
            'max_score' => 'required|numeric|min:0',
            'difficulty' => 'nullable|in:easy,medium,hard'
        ]);
        
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
        
        return $course->questions()->create($data);
    }
    
    /**
     * Sanitize input text
     */
    private function sanitize(string $text): string
    {
        return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generate CSV template
     */
    public function generateTemplate(): string
    {
        $headers = [
            'type',
            'question_text',
            'option_1',
            'option_2',
            'option_3',
            'option_4',
            'correct_answer',
            'max_score',
            'difficulty',
            'scenario_context',
            'image_url'
        ];
        
        $example = [
            'mcq',
            'What is Laravel?',
            'PHP Framework',
            'JavaScript Library',
            'Database',
            'CMS',
            '1',
            '1',
            'easy',
            '',
            ''
        ];
        
        $csv = implode(',', $headers) . "\n";
        $csv .= implode(',', $example) . "\n";
        
        return $csv;
    }
}
