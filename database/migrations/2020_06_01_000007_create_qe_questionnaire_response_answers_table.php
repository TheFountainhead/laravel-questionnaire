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

        if (Schema::hasTable($p.'questionnaire_response_answers')) {
            return;
        }

        Schema::create($p.'questionnaire_response_answers', function (Blueprint $table) use ($p) {
            $table->id();
            $table->unsignedBigInteger('questionnaire_response_id');
            $table->unsignedBigInteger('questionnaire_question_id');
            $table->unsignedBigInteger('questionnaire_option_id');
            $table->timestamps();

            $table->foreign('questionnaire_response_id', $p.'qra_response_fk')->references('id')->on($p.'questionnaire_responses')->cascadeOnDelete();
            $table->foreign('questionnaire_question_id', $p.'qra_question_fk')->references('id')->on($p.'questionnaire_questions')->cascadeOnDelete();
            $table->foreign('questionnaire_option_id', $p.'qra_option_fk')->references('id')->on($p.'questionnaire_options')->cascadeOnDelete();
        });
    }

    protected function prefix(): string
    {
        return config('questionnaire.table_prefix', 'qe_');
    }
};
