<?php

namespace TheFountainhead\Questionnaire\Actions;

use TheFountainhead\Questionnaire\Models\QuestionnaireResponse;
use TheFountainhead\Questionnaire\Models\QuestionnaireRiskProfile;

class CalculateQuestionnaireScore
{
    /** @return array{weighted_score: string, risk_profile: QuestionnaireRiskProfile|null, categories: array} */
    public function handle(QuestionnaireResponse $response): array
    {
        $response->load([
            'answers.option.question.category',
            'questionnaire.categories.questions',
            'questionnaire.riskProfiles',
        ]);

        $categoryScores = [];

        foreach ($response->questionnaire->categories as $category) {
            $rawScore = 0;
            $hasQuestions = false;

            foreach ($category->questions as $question) {
                if (! $question->is_scored) {
                    continue;
                }

                $hasQuestions = true;

                $selectedAnswers = $response->answers
                    ->where('questionnaire_question_id', $question->id);

                foreach ($selectedAnswers as $answer) {
                    $rawScore += $answer->option->points;
                }
            }

            if (! $hasQuestions) {
                continue;
            }

            $weightedScore = $rawScore * $category->weight;

            $categoryScores[] = [
                'name' => $category->name,
                'weight' => $category->weight,
                'raw_score' => $rawScore,
                'weighted_score' => number_format($weightedScore, 2, '.', ''),
            ];
        }

        $totalWeightedScore = array_sum(array_map(
            fn (array $cat) => (float) $cat['weighted_score'],
            $categoryScores
        ));

        $formattedScore = number_format($totalWeightedScore, 2, '.', '');

        $riskProfile = $response->questionnaire->riskProfiles
            ->first(function (QuestionnaireRiskProfile $profile) use ($totalWeightedScore) {
                $aboveMin = $totalWeightedScore >= $profile->min_score;
                $belowMax = $profile->max_score === null || $totalWeightedScore < $profile->max_score;

                return $aboveMin && $belowMax;
            });

        return [
            'weighted_score' => $formattedScore,
            'risk_profile' => $riskProfile,
            'categories' => $categoryScores,
        ];
    }
}
