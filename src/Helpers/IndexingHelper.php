<?php

namespace Firesphere\SearchBackend\Helpers;

use SilverStripe\Core\Config\Configurable;

class IndexingHelper
{
    use Configurable;

    /**
     * @var int Length of each to-index batches
     */
    protected static $batch_length;

    /**
     * @var int amount of CPU cores
     */
    protected static $cores;

    public static function getCores(): int
    {
        // Always be on the safe side, use only 1 core
        return self::$cores ?? 1;
    }

    public static function setCores(int $cores): void
    {
        self::$cores = $cores;
    }

    /**
     * Check the amount of groups and the total against the isGroup check.
     *
     * @param bool $isGroup Is it a specific group
     * @param string $class Class to check
     * @param int $group Current group to index
     * @return array
     */
    public static function getGroupSettings(bool $isGroup, string $class, int $group): array
    {
        $totalGroups = (int)ceil($class::get()->count() / self::getBatchLength());
        $groups = $isGroup ? ($group + self::getCores() - 1) : $totalGroups;

        return [$totalGroups, $groups];
    }

    public static function getBatchLength(): int
    {
        if (!self::$batch_length) {
            self::$batch_length = self::config()->get('batchLength') ?? 10;
        }
        return self::$batch_length;
    }

    public static function setBatchLength(int $batch_length): void
    {
        self::$batch_length = $batch_length;
    }

}
