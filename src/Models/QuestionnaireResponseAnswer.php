<?php

namespace TheFountainhead\Questionnaire\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireResponseAnswer extends Model
{
    public function getTable(): string
    {
        return config('questionnaire.table_prefix', 'qe_').'questionnaire_response_answers';
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireResponse::class, 'questionnaire_response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireQuestion::class, 'questionnaire_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireOption::class, 'questionnaire_option_id');
    }
}
