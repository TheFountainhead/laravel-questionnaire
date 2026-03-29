<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix().'questionnaire_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained($this->prefix().'questionnaires')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained($this->getSubjectTable())->cascadeOnDelete();
            $table->foreignId('completed_by')->constrained($this->getUserTable())->cascadeOnDelete();
            $table->decimal('weighted_score', 5, 2)->nullable();
            $table->foreignId('questionnaire_risk_profile_id')->nullable()->constrained($this->prefix().'questionnaire_risk_profiles')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->index(['subject_id', 'completed_at']);
        });
    }

    protected function getSubjectTable(): string
    {
        return (new (config('questionnaire.models.subject')))->getTable();
    }

    protected function getUserTable(): string
    {
        return (new (config('questionnaire.models.user')))->getTable();
    }

    protected function prefix(): string
    {
        return config('questionnaire.table_prefix', 'qe_');
    }
};
