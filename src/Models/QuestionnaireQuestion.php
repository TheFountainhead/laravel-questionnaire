<?php

namespace TheFountainhead\Questionnaire\Models;

use TheFountainhead\Questionnaire\Enums\QuestionnaireQuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireQuestion extends Model
{
    public function getTable(): string
    {
        return config('questionnaire.table_prefix', 'qe_').'questionnaire_questions';
    }

    protected function casts(): array
    {
        return [
            'type' => QuestionnaireQuestionType::class,
            'is_scored' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireCategory::class, 'questionnaire_category_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionnaireOption::class)->orderBy('sort_order');
    }
}
