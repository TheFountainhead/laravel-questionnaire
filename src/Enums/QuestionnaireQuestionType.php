<?php

namespace TheFountainhead\Questionnaire\Enums;

enum QuestionnaireQuestionType: string
{
    case SingleChoice = 'single_choice';
    case MultiChoice = 'multi_choice';

    public function label(): string
    {
        return match ($this) {
            self::SingleChoice => __('Single Choice'),
            self::MultiChoice => __('Multiple Choice'),
        };
    }
}
