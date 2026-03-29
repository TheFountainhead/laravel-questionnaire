<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix().'questionnaire_response_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_response_id')->constrained($this->prefix().'questionnaire_responses')->cascadeOnDelete();
            $table->foreignId('questionnaire_question_id')->constrained($this->prefix().'questionnaire_questions')->cascadeOnDelete();
            $table->foreignId('questionnaire_option_id')->constrained($this->prefix().'questionnaire_options')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    protected function prefix(): string
    {
        return config('questionnaire.table_prefix', 'qe_');
    }
};
