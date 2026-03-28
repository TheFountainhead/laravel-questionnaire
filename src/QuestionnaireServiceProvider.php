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
        if ($this->app->runningInConsole()) {
            $this->publishesMigrations([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'questionnaire-migrations');

            $this->publishes([
                __DIR__.'/../config/questionnaire.php' => config_path('questionnaire.php'),
            ], 'questionnaire-config');

            $this->publishes([
                __DIR__.'/../src/Notifications/HighRiskClassification.php' => app_path('Notifications/HighRiskClassification.php'),
            ], 'questionnaire-notifications');
        }
    }
}
