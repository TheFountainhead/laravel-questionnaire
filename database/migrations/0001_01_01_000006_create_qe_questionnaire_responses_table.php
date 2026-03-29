<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $p = $this->prefix();

        Schema::create($p.'questionnaire_responses', function (Blueprint $table) use ($p) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained($p.'questionnaires')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained($this->getSubjectTable())->cascadeOnDelete();
            $table->foreignId('completed_by')->constrained($this->getUserTable())->cascadeOnDelete();
            $table->decimal('weighted_score', 5, 2)->nullable();
            $table->unsignedBigInteger('questionnaire_risk_profile_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->foreign('questionnaire_risk_profile_id', $p.'qr_risk_profile_fk')->references('id')->on($p.'questionnaire_risk_profiles')->nullOnDelete();
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
