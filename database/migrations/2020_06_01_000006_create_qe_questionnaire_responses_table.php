<?php

/*
 * 🚨 TIDSSTEMPLET ER BEVIDST 2020_06_01 — laeg det ALDRIG tilbage til
 * 0001_01_01.
 *
 * Praefikset 0001_01_01 er Laravels konvention for framework-migrationer der
 * skal koere ALLERFOERST. Men denne pakkes tabeller har en fremmednoegle UDAD
 * til vaertsapplikationens company-tabel (config('questionnaire.models.company'),
 * typisk `teams`), og den kan pr. definition ikke eksistere endnu paa det
 * tidspunkt.
 *
 * Maalt 24-08-2026 mod en frisk MySQL 8.4:
 *   SQLSTATE[HY000] 1824: Failed to open the referenced table 'teams'
 * Migrationen stoppede paa nr. 2 af ~610; kun 2 tabeller blev oprettet.
 *
 * 🔑 Hvorfor ingen opdagede det foer: sqlite haandhaever ikke fremmednoegler
 * ved ALTER TABLE som standard, og vaertsprojektets CI koerer sqlite. Fejlen
 * var derfor usynlig fra pakken blev tilfoejet til foerste MySQL-opsaetning
 * fra bunden.
 *
 * 2020_06_01 ligger efter Jetstream/Teams (2020_05_21) og foer alt moderne, saa
 * raekkefoelgen holder uanset hvornaar vaertsappen selv blev startet.
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
