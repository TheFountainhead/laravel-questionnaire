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
        if (Schema::hasTable($this->prefix().'questionnaire_categories')) {
            return;
        }

        Schema::create($this->prefix().'questionnaire_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained($this->prefix().'questionnaires')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('weight', 3, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    protected function prefix(): string
    {
        return config('questionnaire.table_prefix', 'qe_');
    }
};
