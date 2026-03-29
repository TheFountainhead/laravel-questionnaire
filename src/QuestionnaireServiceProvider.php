<?php

namespace TheFountainhead\Questionnaire;

use Illuminate\Support\ServiceProvider;

class QuestionnaireServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/questionnaire.php', 'questionnaire');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/questionnaire.php' => config_path('questionnaire.php'),
            ], 'questionnaire-config');

            $this->publishes([
                __DIR__.'/../src/Notifications/HighRiskClassification.php' => app_path('Notifications/HighRiskClassification.php'),
            ], 'questionnaire-notifications');
        }
    }
}
