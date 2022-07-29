<?php

namespace Hellomayaagency\Enso\Dummies\Traits;

use Hellomayaagency\Enso\Dummies\Contracts\HasDummiesContract;
use Hellomayaagency\Enso\Dummies\Exceptions\DummiesException;
use Illuminate\Database\Eloquent\Builder;

/**
 * Adds functionality to a model to allow for the concept of dummy users. These
 * records will be hidden by a global query scope, but can still exist in the
 * database to build relationships with.
 */
trait HasDummies
{
    /**
     * Trait configuration on model booting
     *
     * @return void
     */
    public static function bootHasDummies(): void
    {
        static::addGlobalScope('whereNotDummy', function (Builder $builder) {
            $builder->whereNotDummy();
        });
    }

    /**
     * Create a dummy model without triggering lifecycle hooks.
     *
     * @param array $identifiers
     * @param array $attributes
     *
     * @return self
     */
    public static function createDummy(array $identifiers, array $attributes = []): self
    {
        return static::withoutEvents(function () use ($identifiers, $attributes) {
            $dummy = static::withoutGlobalScope('whereNotDummy')->firstOrNew($identifiers);

            if ($dummy->exists && !$dummy->isDummy()) {
                throw new DummiesException('Cannot create a dummy for an existing model');
            }

            $dummy->fill(
                array_merge(
                    (new static)->getDummyRequiredAttributes(),
                    $attributes
                )
            )->save();

            return $dummy;
        });
    }

    /**
     * The Name of the column that the boolean determinant is stored in.
     *
     * @return string
     */
    public function getDummyColumnName(): string
    {
        return 'dummy';
    }

    /**
     * The minumum attributes that are required to create a dummy model in the
     * database. This is used to set placeholder content for columns that are
     * neither nullable or have defaults set.
     *
     * @return array
     */
    protected function getDummyRequiredAttributes(): array
    {
        return [
            $this->getDummyColumnName() => true,
        ];
    }

    /**
     * Check whether this model is a dummy
     *
     * @return boolean
     */
    public function isDummy(): bool
    {
        return $this->{$this->getDummyColumnName()};
    }

    /**
     * Query scope to limit queries to only those records that are dummies.
     *
     * @param Builder $query
     *
     * @return void
     */
    public function scopeWhereDummy(Builder $query): void
    {
        $query->where($this->getDummyColumnName(), true);
    }

    /**
     * Query scope to limit queries to only those records that are not dummies.
     *
     * @param Builder $query
     *
     * @return void
     */
    public function scopeWhereNotDummy(Builder $query): void
    {
        $query->where($this->getDummyColumnName(), false);
    }

    /**
     * Removes the global scope so that all records are returned.
     *
     * @param Builder $query
     *
     * @return void
     */
    public function scopeWithDummies(Builder $query): void
    {
        $query->withoutGlobalScope('whereNotDummy');
    }

    /**
     * Updates a Dummy or creates a new Model with the given properties, firing
     * eloquent events to simulate the creation process when models are
     * 'upgraded' from dummies to full records.
     *
     * @param array $identifiers
     * @param array $attributes
     *
     * @return HasDummiesContract
     */
    public static function updateDummyOrCreate(array $identifiers, array $attributes = []): HasDummiesContract
    {
        $instance = new static;

        $model = static::withoutGlobalScope('whereNotDummy')->updateOrCreate(
            $identifiers,
            array_merge(
                $attributes,
                [
                    $instance->getDummyColumnName() => false,
                ]
            )
        );

        if (!$model->wasRecentlyCreated) {
            event('eloquent.creating: ' . static::class, $model);
            event('eloquent.created: ' . static::class, $model);
        }

        return $model;
    }
}

