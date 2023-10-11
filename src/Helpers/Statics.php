<?php
/**
 * class Statics|Firesphere\SearchBackend\Helpers\Statics TypeMap support
 *
 * @package Firesphere\Search\Backend
 * @author Simon `Firesphere` Erkelens; Marco `Sheepy` Hermo
 * @copyright Copyright (c) 2018 - now() Firesphere & Sheepy
 */

namespace Firesphere\SearchBackend\Helpers;

use SilverStripe\Core\Config\Configurable;

/**
 * Class Statics
 * Typemap static helper
 *
 * @package Firesphere\Search\Backend
 */
class Statics
{
    use Configurable;

    /**
     * @var array map SilverStripe DB types to Solr types
     */
    protected static $typemap;

    /**
     * Get the typemap for Solr
     * Note that this is very Solr oriented at the moment
     *
     * @return array
     */
    public static function getTypeMap()
    {
        return static::config()->get('typemap');
    }
}
