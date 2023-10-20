<?php
/**
 * Trait CoreIndexTrait|Firesphere\SearchBackend\Traits\CoreIndexTrait Used to extract methods from the
 * {@link \Firesphere\SearchBackend\Indexes\CoreIndex} to make the code more readable
 *
 * @package Firesphere\Search\Backend
 * @author Simon `Firesphere` Erkelens; Marco `Sheepy` Hermo
 * @copyright Copyright (c) 2018 - now() Firesphere & Sheepy
 */

namespace Firesphere\SearchBackend\Traits\IndexingTraits;

use ReflectionClass;
use ReflectionException;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBString;
use Solarium\Core\Client\Client as SolrClient;

/**
 * Trait CoreIndexTrait
 * Getters and Setters for the  SolrIndex
 *
 * @package Firesphere\Search\Backend
 */
trait CoreIndexTrait
{
    /**
     * @var SolrClient Query client
     */
    protected $client;
    /**
     * @var string[] Classes to index
     */
    protected $class = [];
    /**
     * @var array Facet fields
     */
    protected $facetFields = [];
    /**
     * @var array Fulltext fields
     */
    protected $fulltextFields = [];
    /**
     * @var array Filterable fields
     */
    protected $filterFields = [];
    /**
     * @var array Sortable fields
     */
    protected $sortFields = [];
    /**
     * @var array Stored fields
     */
    protected $storedFields = [];

    /**
     * @var array Fields to copy to the default fields
     */
    protected $copyFields = [
        '_text' => [
            '*',
        ],
    ];


    /**
     * usedAllFields is used to determine if the addAllFields method has been called
     * This is to prevent a notice if there is no yml.
     *
     * @var bool
     */
    protected $usedAllFields = false;

    /**
     * Add a class to index or query
     * $options is not used anymore, added for backward compatibility
     *
     * @param $class
     * @param array $options unused
     * @return self
     */
    public function addClass($class, $options = []): self
    {
        $this->class[] = $class;

        return $this;
    }

    /**
     * Set the classes
     *
     * @param array $class
     * @return $this
     */
    public function setClasses($class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get the client
     *
     * @return SolrClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set/override the client
     *
     * @param SolrClient $client
     * @return $this
     */
    public function setClient($client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Add a facet field
     *
     * @param $field
     * @param array $options
     * @return $this
     */
    public function addFacetField($field, $options): self
    {
        $this->facetFields[$field] = $options;

        if (!in_array($options['Field'], $this->getFilterFields(), true)) {
            $this->addFilterField($options['Field']);
        }

        return $this;
    }

    /**
     * Get the filter fields
     *
     * @return array
     */
    public function getFilterFields(): array
    {
        return $this->filterFields;
    }

    /**
     * Set the filter fields
     *
     * @param array $filterFields
     * @return $this
     */
    public function setFilterFields($filterFields): self
    {
        $this->filterFields = $filterFields;
        foreach ($filterFields as $filterField) {
            $this->addFulltextField($filterField);
        }

        return $this;
    }

    /**
     * Add a filterable field
     *
     * @param $filterField
     * @return $this
     */
    public function addFilterField($filterField): self
    {
        $key = array_search($filterField, $this->getFulltextFields(), true);
        if ($key === false) {
            $this->filterFields[] = $filterField;
        }

        return $this;
    }

    /**
     * Get the fulltext fields
     *
     * @return array
     */
    public function getFulltextFields(): array
    {
        return array_values(
            array_unique(
                $this->fulltextFields
            )
        );
    }

    /**
     * Set the fulltext fields
     *
     * @param array $fulltextFields
     * @return $this
     */
    public function setFulltextFields($fulltextFields): self
    {
        $this->fulltextFields = $fulltextFields;

        return $this;
    }

    /**
     * @return array
     */
    public function getFacetFields()
    {
        return $this->facetFields;
    }

    /**
     * Set the fields to use for faceting
     * @param $fields
     * @return $this
     */
    public function setFacetFields($fields)
    {
        $this->facetFields = $fields;
        foreach ($fields as $field => $option) {
            $this->addFulltextField($option['Field']);
        }

        return $this;
    }

    /**
     * Add a single Fulltext field
     *
     * @param string $fulltextField
     * @param array $options
     * @return $this
     */
    abstract public function addFulltextField($fulltextField, $options = []): self;

    /**
     * Add all text-type fields to the given index
     *
     * @throws ReflectionException
     */
    public function addAllFulltextFields()
    {
        $this->addAllFieldsByType(DBString::class);
    }

    /**
     * Add all database-backed text fields as fulltext searchable fields.
     *
     * For every class included in the index, examines those classes and all parent looking for "DBText" database
     * fields (Varchar, Text, HTMLText, etc) and adds them all as fulltext searchable fields.
     *
     * Note, there is no check on boosting etc. That needs to be done manually.
     *
     * @param string $dbType
     * @throws ReflectionException
     */
    protected function addAllFieldsByType($dbType = DBString::class): void
    {
        $this->usedAllFields = true;
        $classes = $this->getClasses();
        foreach ($classes as $key => $class) {
            $fields = DataObject::getSchema()->databaseFields($class, true);

            $this->addFulltextFieldsForClass($fields, $dbType);
        }
    }

    /**
     * Get classes
     *
     * @return array
     */
    public function getClasses(): array
    {
        return $this->class;
    }

    /**
     * Add all fields of a given type to the index
     *
     * @param array $fields The fields on the DataObject
     * @param string $dbType Class type the reflection should extend
     * @throws ReflectionException
     */
    protected function addFulltextFieldsForClass(array $fields, $dbType = DBString::class): void
    {
        foreach ($fields as $field => $type) {
            $pos = strpos($type, '(');
            if ($pos !== false) {
                $type = substr($type, 0, $pos);
            }
            $conf = Config::inst()->get(Injector::class, $type);
            $ref = new ReflectionClass($conf['class']);
            if ($ref->isSubclassOf($dbType)) {
                $this->addFulltextField($field);
            }
        }
    }

    /**
     * Add all date-type fields to the given index
     *
     * @throws ReflectionException
     */
    public function addAllDateFields()
    {
        $this->addAllFieldsByType(DBDate::class);
    }

    /**
     * Add a field to sort on
     *
     * @param $sortField
     * @return $this
     */
    public function addSortField($sortField): self
    {
        if (!in_array($sortField, $this->getFulltextFields(), true) &&
            !in_array($sortField, $this->getFilterFields(), true)
        ) {
            $this->addFulltextField($sortField);
            $this->sortFields[] = $sortField;
        }

        $this->setSortFields(array_unique($this->getSortFields()));

        return $this;
    }

    /**
     * Get the sortable fields
     *
     * @return array
     */
    public function getSortFields(): array
    {
        return $this->sortFields;
    }

    /**
     * Set/override the sortable fields
     *
     * @param array $sortFields
     * @return $this
     */
    public function setSortFields($sortFields): self
    {
        $this->sortFields = $sortFields;
        foreach ($sortFields as $sortField) {
            $this->addFulltextField($sortField);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getStoredFields(): array
    {
        return $this->storedFields;
    }

    /**
     * Stub to be compatible with Solr.
     * @param array $storedFields
     * @return $this
     */
    public function setStoredFields(array $storedFields)
    {
        $this->storedFields = $storedFields;
        foreach ($storedFields as $storedField) {
            $this->addFulltextField($storedField);
        }

        return $this;
    }

    /**
     * Add a copy field
     *
     * @param string $field Name of the copyfield
     * @param array $options Array of all fields that should be copied to this copyfield
     * @return $this
     */
    public function addCopyField($field, $options): self
    {
        $this->copyFields[$field] = $options;

        return $this;
    }

    /**
     * Return the copy fields
     *
     * @return array
     */
    public function getCopyFields(): array
    {
        return $this->copyFields;
    }

    /**
     * Set the copy fields
     *
     * @param array $copyField
     * @return $this
     */
    public function setCopyFields($copyField): self
    {
        $this->copyFields = $copyField;

        return $this;
    }

}
