<?php

namespace Hellomayaagency\Enso\Dummies;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EnsoDummiesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('enso-dummies')
            ->hasMigration('add_dummy_column_to_users');
    }

    public function packageBooted()
    {
        Rule::macro('uniqueExcludingDummies', function (string $class, string $column = 'NULL') {
            $instance = new $class;

            return (new Unique(
                $instance->getTable(),
                $column
            ))->where($instance->getDummyColumnName(), false);
        });
    }
}
