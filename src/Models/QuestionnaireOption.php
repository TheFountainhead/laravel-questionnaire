<?php

namespace TheFountainhead\Questionnaire\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireOption extends Model
{
    protected $fillable = [
        'questionnaire_question_id',
        'text',
        'points',
        'sort_order',
    ];

    public function getTable(): string
    {
        return config('questionnaire.table_prefix', 'qe_').'questionnaire_options';
    }

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireQuestion::class, 'questionnaire_question_id');
    }
}
