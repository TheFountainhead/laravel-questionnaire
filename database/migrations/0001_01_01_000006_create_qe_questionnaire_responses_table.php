<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $p = $this->prefix();

        if (Schema::hasTable($p.'questionnaire_responses')) {
            return;
        }

        $subjectTable = $this->getSubjectTable();
        $userTable = $this->getUserTable();
        $subjectTableExists = Schema::hasTable($subjectTable);
        $userTableExists = Schema::hasTable($userTable);

        Schema::create($p.'questionnaire_responses', function (Blueprint $table) use ($p, $subjectTable, $userTable, $subjectTableExists, $userTableExists) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained($p.'questionnaires')->cascadeOnDelete();

            // Subject/user tables may not exist yet when package migrations run
            // before application migrations. Create columns without FK constraints.
            if ($subjectTableExists) {
                $table->foreignId('subject_id')->constrained($subjectTable)->cascadeOnDelete();
            } else {
                $table->unsignedBigInteger('subject_id');
            }

            if ($userTableExists) {
                $table->foreignId('completed_by')->constrained($userTable)->cascadeOnDelete();
            } else {
                $table->unsignedBigInteger('completed_by');
            }

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
