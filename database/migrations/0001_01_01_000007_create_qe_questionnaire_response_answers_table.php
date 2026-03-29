<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $p = $this->prefix();

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
