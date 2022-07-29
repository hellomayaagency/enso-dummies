<?php

namespace Hellomayaagency\Enso\Dummies\Tests;

use Hellomayaagency\Enso\Dummies\EnsoDummiesServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            function (string $modelName) {
                return 'Hellomayaagency\\Enso\\Dummies\\Database\\Factories\\'. class_basename($modelName) .'Factory';
            }
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            EnsoDummiesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_enso-dummies_table.php.stub';
        $migration->up();
        */
    }
}
