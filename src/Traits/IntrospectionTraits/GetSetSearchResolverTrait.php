<?php
/**
 * Trait GetSetSearchResolverTrait|Firesphere\SearchBackend\Traits\GetSetSearchResolverTrait Used to extract methods from
 * the {@link \Firesphere\SearchBackend\Helpers\FieldResolver} to make the code more readable
 *
 * @package Firesphere\Search\Backend
 * @author Simon `Firesphere` Erkelens; Marco `Sheepy` Hermo
 * @copyright Copyright (c) 2018 - now() Firesphere & Sheepy
 */

namespace Firesphere\SearchBackend\Traits\IntrospectionTraits;

use Firesphere\SolrSearch\Indexes\BaseIndex as SolrIndex;

/**
 * Setters and getters for the introspection.
 *
 * Setters and getters to help with introspection/resolving, it's fairly simple, but extracted
 * so it's cleaner to read the code
 *
 * @package Firesphere\Search\Backend
 */
trait GetSetSearchResolverTrait
{
    /**
     * @var SolrIndex Index to use
     */
    protected $index;

    /**
     * Get the current index
     *
     * @return SolrIndex
     */
    public function getIndex(): SolrIndex
    {
        return $this->index;
    }

    /**
     * Set the current index
     *
     * @param SolrIndex $index
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }
}
