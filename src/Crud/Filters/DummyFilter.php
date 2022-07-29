<?php

namespace Hellomayaagency\Enso\Dummies\Crud\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Yadda\Enso\Crud\Filters\BaseFilters\SelectFilter;

class DummyFilter extends SelectFilter
{
    /**
     * Settings to apply to the filter
     *
     * @var array
     */
    protected $settings = [
        'allow_empty' => false,
        'show_labels' => false,
    ];

    public function __construct($config)
    {
        $label_by = Config::get('enso.settings.select_label_by');
        $track_by = Config::get('enso.settings.select_track_by');

        $this->setSettings([
            'label' => $label_by,
            'track_by' => $track_by,
        ], true);

        $singular = $config->getNameSingular();
        $plural = $config->getNamePlural();

        $this->default([$track_by => 'full', $label_by => 'Full ' . $plural]);
        $this->label($singular . ' state');
        $this->options([
            'all' => 'All ' . $plural,
            'full' => 'Full ' . $plural,
            'dummy' => 'Dummy ' . $plural,
        ]);
        $this->setProps([
            'help-text' => 'Search for ' . $plural . ' in this state',
        ], true);
        $this->access('access-dummy-' . strtolower(Str::kebab($plural)));
    }

    /**
     * Apply columns to the given query as a self-contained where statement
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return void
     */
    protected function applyQueryModifications(Builder $query, $value): void
    {
        switch ($value) {
            case 'full':
                $query->whereNotDummy();
                break;
            case 'dummy':
                $query->whereDummy();
                break;
            case 'all':
            default;
                break;
        }
    }

    /**
     * Applies an alternative query modification if the User doesn't have access
     * to this filter.
     *
     * @param Builder $query
     *
     * @return void
     */
    protected function applyRestrictedQueryModifications(Builder $query): void
    {
        if ($this->relationship_name) {
            $this->applyAsRelationship($query, 'full');
        } else {
            $this->applyQueryModifications($query, 'full');
        }
    }
}
