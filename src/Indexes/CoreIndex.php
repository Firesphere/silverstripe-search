<?php

namespace Firesphere\SearchBackend\Indexes;

use Firesphere\SearchBackend\Queries\CoreQuery;
use Firesphere\SearchBackend\Traits\IndexingTraits\CoreIndexTrait;
use Firesphere\SolrSearch\Queries\SolrQuery;
use LogicException;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Dev\Deprecation;

abstract class CoreIndex
{
    use Extensible;
    use Configurable;
    use CoreIndexTrait;

    /**
     * Field types that can be added
     * Used in init to call build methods from configuration yml
     *
     * @array
     */
    protected static $fieldTypes = [
        'FulltextFields',
        'SortFields',
        'FilterFields',
        'BoostedFields',
        'CopyFields',
        'DefaultField',
        'FacetFields',
        'StoredFields',
    ];

    /**
     * Required to initialise the fields.
     * It's loaded in to the non-static properties for backward compatibility with FTS
     * Also, it's a tad easier to use this way, loading the other way around would be very
     * memory intensive, as updating the config for each item is not efficient
     */
    public function init()
    {
        $config = static::config()->get($this->getIndexName());
        if (!$config) {
            Deprecation::notice('5', 'Please set an index name and use a config yml');
        }

        if (!empty($this->getClasses())) {
            if (!$this->usedAllFields) {
                Deprecation::notice('5', 'It is advised to use a config YML for most cases');
            }

            return;
        }

        $this->initFromConfig($config);
    }

    /**
     * Get the name of this index.
     *
     * @return string
     */
    abstract public function getIndexName(): string;

    /**
     * Generate the config from yml if possible
     * @param array|null $config
     */
    protected function initFromConfig($config): void
    {
        if (!$config || !array_key_exists('Classes', $config)) {
            throw new LogicException('No classes or config to index found!');
        }

        $this->setClasses($config['Classes']);

        // For backward compatibility, copy the config to the protected values
        // Saves doubling up further down the line
        foreach (self::$fieldTypes as $type) {
            if (array_key_exists($type, $config)) {
                $method = 'set' . $type;
                if (method_exists($this, $method)) {
                    $this->$method($config[$type]);
                }
            }
        }
    }

    /**
     * Get all fields that are required for indexing in a unique way
     *
     * @return array
     */
    public function getFieldsForIndexing(): array
    {
        $facets = [];
        foreach ($this->getFacetFields() as $field) {
            $facets[] = $field['Field'];
        }
        // Return values to make the key reset
        // Only return unique values
        // And make it all a single array
        $fields = array_values(
            array_unique(
                array_merge(
                    $this->getFulltextFields(),
                    $this->getSortFields(),
                    $facets,
                    $this->getFilterFields()
                )
            )
        );

        $this->extend('updateFieldsForIndexing', $fields);

        return $fields;
    }

    /**
     * Execute a search of the given query against the
     * current index.
     *
     * @param CoreQuery $query
     * @return mixed
     */
    abstract public function doSearch($query);
}
