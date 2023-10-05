<?php
/**
 * Trait SearchResultGetTrait|Firesphere\SearchBackend\Traits\SearchResultGetTrait Getters for
 * {@link \Firesphere\SearchBackend\Results\SearchResult}
 *
 * @package Firesphere\Elastic\Search
 * @author Simon `Firesphere` Erkelens; Marco `Sheepy` Hermo
 * @copyright Copyright (c) 2018 - now() Firesphere & Sheepy
 */

namespace Firesphere\SearchBackend\Traits;

use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

/**
 * Trait SearchResultGetTrait
 *
 * Getters for search results to keep the {@link SearchResult} class clean.
 *
 * @package Firesphere\Elastic\Search
 */
trait SearchResultGetTrait
{
    /**
     * @var int Total items in the result
     */
    protected $totalItems;

    /**
     * @var ArrayData Facet results
     */
    protected $facets;

    /**
     * @var Highlighting Highlighting
     */
    protected $highlight;

    /**
     * @var ArrayList Spellcheck results
     */
    protected $spellcheck;

    /**
     * @var string Collated spellcheck
     */
    protected $collatedSpellcheck;

    /**
     * Retrieve the facets from the results
     *
     * @return ArrayData
     */
    public function getFacets(): ArrayData
    {
        return $this->facets;
    }

    /**
     * Get the collated spellcheck
     *
     * @return string
     */
    public function getCollatedSpellcheck()
    {
        return $this->collatedSpellcheck;
    }

    /**
     * Get the highlighting
     *
     * @return Highlighting|null
     */
    public function getHighlight()
    {
        return $this->highlight;
    }

    /**
     * Get the spellchecked results
     *
     * @return ArrayList
     */
    public function getSpellcheck(): ArrayList
    {
        return $this->spellcheck;
    }

    /**
     * Total items in the result
     *
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }
}
