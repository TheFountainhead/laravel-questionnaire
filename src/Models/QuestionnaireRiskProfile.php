<?php

namespace TheFountainhead\Questionnaire\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireRiskProfile extends Model
{
    public function getTable(): string
    {
        return config('questionnaire.table_prefix', 'qe_').'questionnaire_risk_profiles';
    }

    protected function casts(): array
    {
        return [
            'min_score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function color(): string
    {
        return match ($this->sort_order) {
            0 => 'green',
            1 => 'yellow',
            2 => 'red',
            default => 'zinc',
        };
    }
}
