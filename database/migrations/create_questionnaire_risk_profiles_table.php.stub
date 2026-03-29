<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->prefix().'questionnaire_risk_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained($this->prefix().'questionnaires')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('min_score', 5, 2);
            $table->decimal('max_score', 5, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    protected function prefix(): string
    {
        return config('questionnaire.table_prefix', 'qe_');
    }
};
