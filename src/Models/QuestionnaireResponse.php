<?php

namespace TheFountainhead\Questionnaire\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireResponse extends Model
{
    protected function casts(): array
    {
        return [
            'weighted_score' => 'decimal:2',
            'completed_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(config('questionnaire.models.subject'));
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(config('questionnaire.models.user'), 'completed_by');
    }

    public function riskProfile(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireRiskProfile::class, 'questionnaire_risk_profile_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionnaireResponseAnswer::class);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }
}
