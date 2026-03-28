<?php

namespace TheFountainhead\Questionnaire\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use TheFountainhead\Questionnaire\Models\Questionnaire;

class CopyQuestionnaireTemplate
{
    public function handle(Questionnaire $template, Model $company): ?Questionnaire
    {
        $existing = Questionnaire::query()
            ->where('company_id', $company->getKey())
            ->where('type', $template->type)
            ->where('is_active', true)
            ->exists();

        if ($existing) {
            return null;
        }

        return DB::transaction(function () use ($template, $company) {
            $copy = Questionnaire::create([
                'company_id' => $company->getKey(),
                'type' => $template->type,
                'name' => $template->name,
                'description' => $template->description,
                'is_template' => false,
                'is_active' => true,
            ]);

            foreach ($template->categories as $category) {
                $newCategory = $copy->categories()->create([
                    'name' => $category->name,
                    'weight' => $category->weight,
                    'sort_order' => $category->sort_order,
                ]);

                foreach ($category->questions as $question) {
                    $newQuestion = $newCategory->questions()->create([
                        'text' => $question->text,
                        'type' => $question->type,
                        'is_scored' => $question->is_scored,
                        'sort_order' => $question->sort_order,
                    ]);

                    foreach ($question->options as $option) {
                        $newQuestion->options()->create([
                            'text' => $option->text,
                            'points' => $option->points,
                            'sort_order' => $option->sort_order,
                        ]);
                    }
                }
            }

            foreach ($template->riskProfiles as $profile) {
                $copy->riskProfiles()->create([
                    'name' => $profile->name,
                    'min_score' => $profile->min_score,
                    'max_score' => $profile->max_score,
                    'sort_order' => $profile->sort_order,
                ]);
            }

            return $copy->load(['categories.questions.options', 'riskProfiles']);
        });
    }
}
