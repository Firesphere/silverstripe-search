<?php

namespace Firesphere\SearchBackend\Indexes;

use SilverStripe\Core\Extensible;

abstract class CoreIndex
{
    use Extensible;

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
     * Get all fields that are required for indexing in a unique way
     *
     * @return array
     */
    public function getFieldsForIndexing(): array
    {
        $facets = [];
        //        foreach ($this->getFacetFields() as $field) {
        //            $facets[] = $field['Field'];
        //        }
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
}
