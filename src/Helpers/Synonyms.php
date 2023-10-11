<?php
/**
 * class Synonyms|Firesphere\SearchBackend\Helpers\Synonyms Get the default UK-US synonyms helper
 *
 * @package Firesphere\Search\Search
 * @author Simon `Firesphere` Erkelens; Marco `Sheepy` Hermo
 * @copyright Copyright (c) 2018 - now() Firesphere & Sheepy
 */

namespace Firesphere\SearchBackend\Helpers;

use SilverStripe\Core\Config\Configurable;

/**
 * Class Synonyms
 * List out UK to US synonyms and synonyms from {@link SiteConfig}
 * Source: @link https://raw.githubusercontent.com/heiswayi/spelling-uk-vs-us/
 *
 * @package Firesphere\Search\Search
 */
class Synonyms
{
    use Configurable;

    /**
     * @var array Synonym list
     */
    protected static $synonyms;

    /**
     * Make the UK to US spelling synonyms as a newline separated string
     * Or any other synonyms defined if the user wishes to do so
     *
     * @param bool $defaults add Default UK-US synonyms to the list
     * @return string
     */
    public static function getSynonymsAsString($defaults = true)
    {
        $result = [];
        foreach (static::getSynonyms($defaults) as $word => $synonym) {
            // Make all synonym strings
            // @todo remove duplicates
            $synonym = implode(',', (array)$synonym);
            $result[] = sprintf('%s,%s', $word, $synonym);
        }

        return implode(PHP_EOL, $result) . PHP_EOL;
    }

    /**
     * Get the available synonyms as an array from config
     * Defaulting to adding the UK to US spelling differences
     *
     * @param bool $defaults adds the UK to US spelling to the list if true
     * @return array
     */
    public static function getSynonyms($defaults = true)
    {
        // If we want the defaults, load it in to the array, otherwise return an empty array
        $usuk = $defaults ? static::config()->get('usuk')['synonyms'] : [];
        $synonyms = static::config()->get('synonyms') ?: [];

        return array_merge($usuk, $synonyms);
    }

    public static function getSynonymsFlattened($defaults = true)
    {
        $synonyms = self::getSynonyms($defaults);
        foreach ($synonyms as $key => &$value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $value = sprintf('%s,%s', $key, $value);
        }

        return $synonyms;
    }
}
