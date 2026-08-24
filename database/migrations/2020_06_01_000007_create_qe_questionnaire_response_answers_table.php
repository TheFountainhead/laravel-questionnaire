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

    public function down(): void
    {
        // 🪤 Uden en down() kaster `migrate:rollback` fatal error — Laravels
        // Migration er abstrakt og har ingen default. Fundet ved review 24-08-2026.
        Schema::dropIfExists($this->prefix().'questionnaire_response_answers');
    }


    protected function prefix(): string
    {
        return config('questionnaire.table_prefix', 'qe_');
    }
};
