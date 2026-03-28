<?php

use Illuminate\Foundation\Auth\User;
use TheFountainhead\Questionnaire\Actions\CalculateQuestionnaireScore;
use TheFountainhead\Questionnaire\Models\Questionnaire;
use TheFountainhead\Questionnaire\Models\QuestionnaireCategory;
use TheFountainhead\Questionnaire\Models\QuestionnaireOption;
use TheFountainhead\Questionnaire\Models\QuestionnaireQuestion;
use TheFountainhead\Questionnaire\Models\QuestionnaireResponse;
use TheFountainhead\Questionnaire\Models\QuestionnaireResponseAnswer;
use TheFountainhead\Questionnaire\Models\QuestionnaireRiskProfile;

beforeEach(function () {
    $this->user = User::create(['name' => 'Test', 'email' => 'test@test.com']);

    $this->questionnaire = Questionnaire::create([
        'company_id' => $this->user->id,
        'type' => 'test',
        'name' => 'Test Questionnaire',
        'is_template' => false,
        'is_active' => true,
    ]);

    $cat1 = QuestionnaireCategory::create([
        'questionnaire_id' => $this->questionnaire->id,
        'name' => 'Category A',
        'weight' => 0.60,
        'sort_order' => 0,
    ]);

    $q1 = QuestionnaireQuestion::create([
        'questionnaire_category_id' => $cat1->id,
        'text' => 'Question 1',
        'type' => 'single_choice',
        'is_scored' => true,
        'sort_order' => 0,
    ]);

    $this->opt1Low = QuestionnaireOption::create([
        'questionnaire_question_id' => $q1->id,
        'text' => 'Low',
        'points' => 1,
        'sort_order' => 0,
    ]);

    $this->opt1High = QuestionnaireOption::create([
        'questionnaire_question_id' => $q1->id,
        'text' => 'High',
        'points' => 4,
        'sort_order' => 1,
    ]);

    $cat2 = QuestionnaireCategory::create([
        'questionnaire_id' => $this->questionnaire->id,
        'name' => 'Category B',
        'weight' => 0.40,
        'sort_order' => 1,
    ]);

    $q2 = QuestionnaireQuestion::create([
        'questionnaire_category_id' => $cat2->id,
        'text' => 'Question 2',
        'type' => 'single_choice',
        'is_scored' => true,
        'sort_order' => 0,
    ]);

    $this->opt2Low = QuestionnaireOption::create([
        'questionnaire_question_id' => $q2->id,
        'text' => 'Low',
        'points' => 1,
        'sort_order' => 0,
    ]);

    $this->opt2High = QuestionnaireOption::create([
        'questionnaire_question_id' => $q2->id,
        'text' => 'High',
        'points' => 3,
        'sort_order' => 1,
    ]);

    QuestionnaireRiskProfile::create([
        'questionnaire_id' => $this->questionnaire->id,
        'name' => 'Lav',
        'min_score' => 0,
        'max_score' => 1.50,
        'sort_order' => 0,
    ]);

    QuestionnaireRiskProfile::create([
        'questionnaire_id' => $this->questionnaire->id,
        'name' => 'Høj',
        'min_score' => 1.50,
        'max_score' => null,
        'sort_order' => 2,
    ]);
});

it('calculates weighted score with low answers', function () {
    $response = QuestionnaireResponse::create([
        'questionnaire_id' => $this->questionnaire->id,
        'subject_id' => $this->user->id,
        'completed_by' => $this->user->id,
    ]);

    QuestionnaireResponseAnswer::create([
        'questionnaire_response_id' => $response->id,
        'questionnaire_question_id' => $this->opt1Low->question->id,
        'questionnaire_option_id' => $this->opt1Low->id,
    ]);

    QuestionnaireResponseAnswer::create([
        'questionnaire_response_id' => $response->id,
        'questionnaire_question_id' => $this->opt2Low->question->id,
        'questionnaire_option_id' => $this->opt2Low->id,
    ]);

    $result = (new CalculateQuestionnaireScore)->handle($response);

    expect($result['weighted_score'])->toBe('1.00');
    expect($result['risk_profile']->name)->toBe('Lav');
    expect($result['categories'])->toHaveCount(2);
});

it('calculates weighted score with high answers', function () {
    $response = QuestionnaireResponse::create([
        'questionnaire_id' => $this->questionnaire->id,
        'subject_id' => $this->user->id,
        'completed_by' => $this->user->id,
    ]);

    QuestionnaireResponseAnswer::create([
        'questionnaire_response_id' => $response->id,
        'questionnaire_question_id' => $this->opt1High->question->id,
        'questionnaire_option_id' => $this->opt1High->id,
    ]);

    QuestionnaireResponseAnswer::create([
        'questionnaire_response_id' => $response->id,
        'questionnaire_question_id' => $this->opt2High->question->id,
        'questionnaire_option_id' => $this->opt2High->id,
    ]);

    $result = (new CalculateQuestionnaireScore)->handle($response);

    expect($result['weighted_score'])->toBe('3.60');
    expect($result['risk_profile']->name)->toBe('Høj');
});
