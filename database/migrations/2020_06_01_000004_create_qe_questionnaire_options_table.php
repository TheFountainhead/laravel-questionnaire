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
        if (Schema::hasTable($this->prefix().'questionnaire_options')) {
            return;
        }

        Schema::create($this->prefix().'questionnaire_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_question_id')->constrained($this->prefix().'questionnaire_questions')->cascadeOnDelete();
            $table->string('text');
            $table->integer('points')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // 🪤 Uden en down() kaster `migrate:rollback` fatal error — Laravels
        // Migration er abstrakt og har ingen default. Fundet ved review 24-08-2026.
        Schema::dropIfExists($this->prefix().'questionnaire_options');
    }


    protected function prefix(): string
    {
        return config('questionnaire.table_prefix', 'qe_');
    }
};
