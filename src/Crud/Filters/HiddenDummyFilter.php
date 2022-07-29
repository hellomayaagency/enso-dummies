<?php

namespace Hellomayaagency\Enso\Dummies\Crud\Filters;

use Hellomayaagency\Enso\Dummies\Crud\Filters\DummyFilter;

class HiddenDummyFilter extends DummyFilter
{
    /**
     * Outputs this instance into a data array for the Crud.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [];
    }
}
