<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsQuestionBank extends Model
{
    protected $table = 'lms_question_banks';

    protected $fillable = [
        'name', 'description', 'institution_id', 'category_id', 'created_by', 'is_shared',
    ];

    protected $casts = ['is_shared' => 'boolean'];

    public function questions()   { return $this->hasMany(LmsQuestionV2::class, 'bank_id'); }
    public function institution() { return $this->belongsTo(LmsInstitution::class); }
}
