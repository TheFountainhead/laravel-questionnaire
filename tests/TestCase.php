<?php

namespace TheFountainhead\Questionnaire\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;
use TheFountainhead\Questionnaire\QuestionnaireServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

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
        $app['config']->set('questionnaire.table_prefix', 'qe_');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getEnvironmentSetUp($app): void
    {
        Model::unguard();
    }
}
