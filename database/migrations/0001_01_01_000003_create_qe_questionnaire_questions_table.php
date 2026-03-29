<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix().'questionnaire_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_category_id')->constrained($this->prefix().'questionnaire_categories')->cascadeOnDelete();
            $table->text('text');
            $table->string('type');
            $table->boolean('is_scored')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    protected function prefix(): string
    {
        return config('questionnaire.table_prefix', 'qe_');
    }
};
