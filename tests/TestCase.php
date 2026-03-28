<?php

namespace TheFountainhead\Questionnaire\Tests;

use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase as BaseTestCase;
use TheFountainhead\Questionnaire\QuestionnaireServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [QuestionnaireServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('questionnaire.models.user', \Illuminate\Foundation\Auth\User::class);
        $app['config']->set('questionnaire.models.company', \Illuminate\Foundation\Auth\User::class);
        $app['config']->set('questionnaire.models.subject', \Illuminate\Foundation\Auth\User::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom($this->prepareMigrations());
    }

    protected function getEnvironmentSetUp($app): void
    {
        Model::unguard();
    }

    /**
     * Copy users table migration and .php.stub package migrations
     * into a single temp directory with proper ordering.
     */
    private function prepareMigrations(): string
    {
        $target = sys_get_temp_dir().'/questionnaire_test_migrations_'.md5(__DIR__);

        if (is_dir($target)) {
            array_map('unlink', glob($target.'/*.php'));
        } else {
            mkdir($target, 0755, true);
        }

        // 1. Users table first (needed for FK constraints)
        copy(
            __DIR__.'/migrations/0001_01_01_000000_create_users_table.php',
            $target.'/0001_01_01_000000_create_users_table.php'
        );

        // 2. Package stubs as .php with ordered timestamps
        $source = realpath(__DIR__.'/../database/migrations');

        foreach (glob($source.'/*.php.stub') as $stub) {
            $filename = basename($stub, '.stub');
            $timestamp = match (true) {
                str_contains($filename, 'create_questionnaires_table') => '0001_01_01_000001_',
                str_contains($filename, 'questionnaire_categories') => '0001_01_01_000002_',
                str_contains($filename, 'questionnaire_questions') => '0001_01_01_000003_',
                str_contains($filename, 'questionnaire_options') => '0001_01_01_000004_',
                str_contains($filename, 'questionnaire_risk_profiles') => '0001_01_01_000005_',
                str_contains($filename, 'questionnaire_responses_table') => '0001_01_01_000006_',
                str_contains($filename, 'questionnaire_response_answers') => '0001_01_01_000007_',
                default => '0001_01_01_000099_',
            };

            copy($stub, $target.'/'.$timestamp.$filename);
        }

        return $target;
    }
}
