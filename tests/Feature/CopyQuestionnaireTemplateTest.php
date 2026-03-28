<?php

use Illuminate\Foundation\Auth\User;
use TheFountainhead\Questionnaire\Actions\CopyQuestionnaireTemplate;
use TheFountainhead\Questionnaire\Models\Questionnaire;
use TheFountainhead\Questionnaire\Models\QuestionnaireCategory;
use TheFountainhead\Questionnaire\Models\QuestionnaireOption;
use TheFountainhead\Questionnaire\Models\QuestionnaireQuestion;
use TheFountainhead\Questionnaire\Models\QuestionnaireRiskProfile;

it('copies a template for a company', function () {
    $company = User::create(['name' => 'Company', 'email' => 'co@test.com']);

    $template = Questionnaire::create([
        'company_id' => null,
        'type' => 'aml',
        'name' => 'AML Template',
        'is_template' => true,
        'is_active' => true,
    ]);

    $cat = QuestionnaireCategory::create([
        'questionnaire_id' => $template->id,
        'name' => 'Test Cat',
        'weight' => 0.50,
        'sort_order' => 0,
    ]);

    $q = QuestionnaireQuestion::create([
        'questionnaire_category_id' => $cat->id,
        'text' => 'Test Q',
        'type' => 'single_choice',
        'is_scored' => true,
        'sort_order' => 0,
    ]);

    QuestionnaireOption::create([
        'questionnaire_question_id' => $q->id,
        'text' => 'Opt A',
        'points' => 1,
        'sort_order' => 0,
    ]);

    QuestionnaireRiskProfile::create([
        'questionnaire_id' => $template->id,
        'name' => 'Low',
        'min_score' => 0,
        'max_score' => 5,
        'sort_order' => 0,
    ]);

    $copy = (new CopyQuestionnaireTemplate)->handle($template, $company);

    expect($copy)->not->toBeNull();
    expect($copy->company_id)->toBe($company->id);
    expect($copy->is_template)->toBeFalse();
    expect($copy->categories)->toHaveCount(1);
    expect($copy->categories->first()->questions->first()->options)->toHaveCount(1);
    expect($copy->riskProfiles)->toHaveCount(1);
});

it('returns null if company already has active questionnaire', function () {
    $company = User::create(['name' => 'Company', 'email' => 'co@test.com']);

    Questionnaire::create([
        'company_id' => $company->id,
        'type' => 'aml',
        'name' => 'Existing',
        'is_template' => false,
        'is_active' => true,
    ]);

    $template = Questionnaire::create([
        'company_id' => null,
        'type' => 'aml',
        'name' => 'Template',
        'is_template' => true,
        'is_active' => true,
    ]);

    expect((new CopyQuestionnaireTemplate)->handle($template, $company))->toBeNull();
});
