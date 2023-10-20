<?php
/**
 *
 * @package Firesphere\Search\Search
 * @author Simon `Firesphere` Erkelens; Marco `Sheepy` Hermo
 * @copyright Copyright (c) 2018 - now() Firesphere & Sheepy
 */

namespace Firesphere\SearchBackend\Models;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\SearchBackend\Models\SearchSynonym
 *
 * @property string $Keyword
 * @property string $Synonym
 */
class SearchSynonym extends DataObject
{
    /**
     * @var string Table name
     */
    private static $table_name = 'SearchSynonym';

    /**
     * @var string Singular name
     */
    private static $singular_name = 'Search synonym';

    /**
     * @var string Plural name
     */
    private static $plural_name = 'Search synonyms';

    /**
     * @var array DB Fields
     */
    private static $db = [
        'Keyword' => 'Varchar(255)',
        'Synonym' => 'Text'
    ];

    /**
     * @var array Summary fields
     */
    private static $summary_fields = [
        'Keyword',
        'Synonym'
    ];

    /**
     * Get the required CMS Fields for this synonym
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->dataFieldByName('Synonym')->setDescription(
            _t(
                __CLASS__ . '.SYNONYM',
                'Create synonyms for a given keyword, add as many synonyms comma separated.'
            )
        );

        return $fields;
    }

    /**
     * Combine this synonym in to a string for the synonyms, like Solr synonyms.txt file
     *
     * @return string
     */
    public function getCombinedSynonym()
    {
        return sprintf("%s,%s", $this->Keyword, $this->Synonym);
    }

    /**
     * Get an ID for usage as reference, e.g. in Solr.
     * Using the Table name as an identifier from the Base synonyms
     * @return string
     */
    public function getModifiedID(): string
    {
        return sprintf("%s-%s", self::$table_name, $this->ID);
    }
}
