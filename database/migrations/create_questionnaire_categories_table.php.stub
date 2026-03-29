<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
