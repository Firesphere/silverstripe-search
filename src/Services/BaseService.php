<?php

namespace Firesphere\SearchBackend\Services;

use ReflectionClass;
use ReflectionException;
use SilverStripe\Core\ClassInfo;

class BaseService
{
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
}
