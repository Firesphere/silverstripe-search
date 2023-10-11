<?php

namespace Firesphere\SearchBackend\Services;

use LogicException;
use ReflectionClass;
use ReflectionException;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Configurable;

class BaseService
{
    use Configurable;

    /**
     * Unique ID in Search engine
     */
    public const ID_FIELD = 'id';
    /**
     * SilverStripe ID of the object
     */
    public const CLASS_ID_FIELD = 'ObjectID';
    /**
     * Name of the field that can be used for queries
     */
    public const CLASSNAME = 'ClassName';

    /**
     * @var array Base indexes that exist
     */
    protected $baseIndexes = [];
    /**
     * @var array Valid indexes out of the base indexes
     */
    protected $validIndexes = [];

    /**
     * @throws ReflectionException
     */
    public function __construct($baseIndexClass)
    {
        $this->baseIndexes = ClassInfo::subclassesFor($baseIndexClass);
        $this->filterIndexes();
    }

    /**
     * Filter enabled indexes down to valid indexes that can be instantiated
     * or are allowed from config
     *
     * @throws ReflectionException
     */
    protected function filterIndexes(): void
    {
        $enabledIndexes = static::config()->get('indexes');
        $enabledIndexes = is_array($enabledIndexes) ? $enabledIndexes : $this->baseIndexes;
        foreach ($this->baseIndexes as $subindex) {
            // If the config of indexes is set, and the requested index isn't in it, skip addition
            // Or, the index simply doesn't exist, also a valid option
            if (!in_array($subindex, $enabledIndexes, true) ||
                !$this->checkReflection($subindex)
            ) {
                continue;
            }
            $this->validIndexes[] = $subindex;
        }
    }


    /**
     * Check if the class is instantiable
     *
     * @param $subindex
     * @return bool
     * @throws ReflectionException
     */
    protected function checkReflection($subindex): bool
    {
        $reflectionClass = new ReflectionClass($subindex);

        return $reflectionClass->isInstantiable();
    }

    public function getValidIndexes($index = null): array
    {
        if ($index && !in_array($index, $this->validIndexes, true)) {
            throw new LogicException('Incorrect index ' . $index);
        }

        if ($index) {
            return [$index];
        }

        // return the array values, to reset the keys
        return array_values($this->validIndexes);
    }

    public function setValidIndexes(array $validIndexes): void
    {
        $this->validIndexes = $validIndexes;
    }
}
