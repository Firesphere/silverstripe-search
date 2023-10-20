<?php

namespace Firesphere\SearchBackend\Factories;

use Exception;
use Firesphere\SearchBackend\Helpers\DataResolver;
use Firesphere\SearchBackend\Helpers\FieldResolver;
use Firesphere\SearchBackend\Indexes\CoreIndex;
use Firesphere\SearchBackend\Traits\LoggerTrait;
use Firesphere\SolrSearch\Indexes\BaseIndex as SolrBaseIndex;
use Psr\Container\NotFoundExceptionInterface;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\UniqueKey\UniqueKeyInterface;

/**
 * Class DocumentFactory
 * Factory to create documents to be pushed to Solr
 *
 * @package Firesphere\Search\Backend
 */
abstract class DocumentCoreFactory
{
    use Extensible;
    use Configurable;
    use LoggerTrait;

    /**
     * @var string Name of the class being indexed
     */
    protected $class;

    /**
     * @var SS_List Items that need to be manufactured into documents
     */
    protected $items;

    /**
     * @var FieldResolver
     */
    protected $fieldResolver;

    /**
     * Set debug mode on or off
     * @var bool
     */
    protected $debug;
    /**
     * @var UniqueKeyInterface
     */
    protected $keyService;

    /**
     * DocumentFactory constructor, sets up the field resolver
     * @throws NotFoundExceptionInterface
     */
    public function __construct()
    {
        $this->fieldResolver = Injector::inst()->get(FieldResolver::class);
        $this->keyService = Injector::inst()->get(UniqueKeyInterface::class);
        $this->logger = $this->getLogger();
    }

    /**
     * Note, it can only take one type of class at a time!
     * So make sure you properly loop and set $class
     *
     * @param array $fields Fields to index
     * @param SolrBaseIndex $index Index to push the documents to
     * @return array Documents to be pushed
     * @throws Exception
     */
    abstract public function buildItems($fields, $index, $update = null): array;

    /**
     * Are we debugging?
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return (bool)$this->debug;
    }

    /**
     * Set to true if debugging should be enabled
     *
     * @param bool $debug
     * @return DocumentCoreFactory
     */
    public function setDebug(bool $debug): DocumentCoreFactory
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

    abstract protected function addDefaultFields($doc, $item);

    /**
     * Show the message about what is being indexed
     *
     * @param CoreIndex $index
     * @throws NotFoundExceptionInterface
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
     * @return string
     */
    public function getClass()
    {
        return $this->class;
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
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param mixed $items
     */
    public function setItems($items): void
    {
        $this->items = $items;
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

    protected function getShortFieldName($name)
    {
        $name = explode('\\', $name);

        return end($name);
    }
}
