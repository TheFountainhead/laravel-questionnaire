<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable($this->prefix().'questionnaires')) {
            return;
        }

        Schema::create($this->prefix().'questionnaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained($this->getCompanyTable())->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_template')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['company_id', 'type']);
        });
    }

    protected function getCompanyTable(): string
    {
        return (new (config('questionnaire.models.company')))->getTable();
    }

    protected function prefix(): string
    {
        return config('questionnaire.table_prefix', 'qe_');
    }
};
