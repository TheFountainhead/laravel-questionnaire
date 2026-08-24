<?php

/*
 * 🚨 TIDSSTEMPLET ER BEVIDST 2020_06_01 — laeg det ALDRIG tilbage til
 * 0001_01_01. Fuld begrundelse i
 * 2020_06_01_000001_create_qe_questionnaires_table.php.
 *
 * Kort: pakkens tabeller har fremmednoegler UDAD til vaertens tabeller, som
 * ikke findes naar 0001_01_01 koerer (MySQL: 1824). Udadgaaende noegler
 * saettes af 2026_08_24_120000_add_host_foreign_keys_to_qe_tables.
 */

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

    public function down(): void
    {
        // 🪤 Uden en down() kaster `migrate:rollback` fatal error — Laravels
        // Migration er abstrakt og har ingen default. Fundet ved review 24-08-2026.
        Schema::dropIfExists($this->prefix().'questionnaire_responses');
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
