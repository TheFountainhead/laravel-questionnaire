<?php

namespace TheFountainhead\Questionnaire\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Questionnaire extends Model
{
    public function getTable(): string
    {
        return config('questionnaire.table_prefix', 'qe_').'questionnaires';
    }

    protected function casts(): array
    {
        return [
            'is_template' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(config('questionnaire.models.company'));
    }

    public function categories(): HasMany
    {
        return $this->hasMany(QuestionnaireCategory::class)->orderBy('sort_order');
    }

    public function riskProfiles(): HasMany
    {
        return $this->hasMany(QuestionnaireRiskProfile::class)->orderBy('sort_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeTemplates(Builder $query): Builder
    {
        return $query->where('is_template', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
