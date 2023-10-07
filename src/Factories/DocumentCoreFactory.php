<?php

namespace Firesphere\SearchBackend\Factories;

use Exception;
use Firesphere\ElasticSearch\Indexes\BaseIndex as ElasticBaseIndex;
use Firesphere\SearchBackend\Extensions\DataObjectSearchExtension;
use Firesphere\SearchBackend\Services\BaseService;
use Firesphere\SolrSearch\Helpers\DataResolver;
use Firesphere\SolrSearch\Indexes\BaseIndex as SolrBaseIndex;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\SS_List;
use Solarium\QueryType\Update\Query\Document;

/**
 * Class DocumentFactory
 * Factory to create documents to be pushed to Solr
 *
 * @package Firesphere\Solr\Search
 */
abstract class DocumentCoreFactory
{
    use Extensible;
    use Configurable;
    /**
     * @var string Name of the class being indexed
     */
    protected $class;

    /**
     * @var SS_List Items that need to be manufactured into documents
     */
    protected $items;
    /**
     * Note, it can only take one type of class at a time!
     * So make sure you properly loop and set $class
     *
     * @param array $fields Fields to index
     * @param ElasticBaseIndex|SolrBaseIndex $index Index to push the documents to
     * @return array Documents to be pushed
     * @throws Exception
     */
    abstract public function buildItems($fields, $index, $update = null): array;

    /**
     * @param mixed $items
     */
    public function setItems($items): void
    {
        $this->items = $items;
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param mixed $class
     */
    public function setClass($class): void
    {
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Show the message about what is being indexed
     *
     * @param ElasticBaseIndex|SolrBaseIndex $index
     */
    protected function indexGroupMessage($index): void
    {
        $debugString = sprintf(
            'Indexing %s on %s (%s items)%s',
            $this->getClass(),
            $index->getIndexName(),
            $this->getItems()->count(),
            PHP_EOL
        );
        $this->getLogger()->info($debugString);
    }

    /**
     * Add fields that should always be included
     *
     * @param Document|array $doc Solr Document
     * @param DataObject|DataObjectSearchExtension $item Item to get the data from
     */
    protected function addDefaultFields(array|Document $doc, $item)
    {
        $doc->setKey(BaseService::ID_FIELD, $item->ClassName . '-' . $item->ID);
        $doc->addField(BaseService::CLASS_ID_FIELD, $item->ID);
        $doc->addField('ClassName', $item->ClassName);
        $hierarchy = ClassInfo::ancestry($item);
        $classHierarchy = [];
        foreach ($hierarchy as $lower => $camel) {
            $classHierarchy[] = $camel;
        }
        $doc->addField('ClassHierarchy', $classHierarchy);
        $doc->addField('ViewStatus', $item->getViewStatus());
        $this->extend('updateDefaultFields', $doc, $item);
    }

    /**
     * Determine if the given object is one of the given type
     *
     * @param string|DataObject $class Class to compare
     * @param array|string $base Class or list of base classes
     * @return bool
     */
    protected function classIs($class, $base): bool
    {
        $base = is_array($base) ? $base : [$base];

        foreach ($base as $nextBase) {
            if ($this->classEquals($class, $nextBase)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a base class is an instance of the expected base group
     *
     * @param string|DataObject $class Class to compare
     * @param string $base Base class
     * @return bool
     */
    protected function classEquals($class, $base): bool
    {
        return $class === $base || ($class instanceof $base);
    }

    /**
     * Use the DataResolver to find the value(s) for a field.
     * Returns an array of values, and if it's multiple, it becomes a long array
     *
     * @param DataObject $object Object to resolve
     * @param array $options Customised options
     * @return array
     */
    protected function getValuesForField($object, $options): array
    {
        try {
            $valuesForField = [DataResolver::identify($object, $options['fullfield'])];
        } catch (Exception $error) {
            // @codeCoverageIgnoreStart
            $valuesForField = [];
            // @codeCoverageIgnoreEnd
        }

        return $valuesForField;
    }

    /**
     * Push field to a document
     *
     * @param Document $doc Solr document
     * @param array $options Custom options
     * @param string $type Type of Solr field
     * @param DBField|string|null $value Value(s) of the field
     */
    protected function addToDoc($doc, $options, $type, $value): void
    {
        /* Solr requires dates in the form 1995-12-31T23:59:59Z, so we need to normalize to GMT */
        if (($value && $type === 'tdate') || $value instanceof DBDate) {
            $value = gmdate('Y-m-d\TH:i:s\Z', strtotime($value));
        }

        $name = getShortFieldName($options['name']);

        $doc->addField($name, $value, $options['boost'], Document::MODIFIER_SET);
    }

    /**
     * Are we debugging?
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Set to true if debugging should be enabled
     *
     * @param bool $debug
     * @return DocumentFactory
     */
    public function setDebug(bool $debug): DocumentFactory
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * @return mixed|object|Injector
     */
    public function getFieldResolver(): mixed
    {
        return $this->fieldResolver;
    }

    /**
     * @param mixed|object|Injector $fieldResolver
     */
    public function setFieldResolver(mixed $fieldResolver): void
    {
        $this->fieldResolver = $fieldResolver;
    }
}
