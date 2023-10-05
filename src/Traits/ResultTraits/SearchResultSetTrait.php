<?php
/**
 * Trait SearchResultSetTrait|Firesphere\SearchBackend\Traits\SearchResultSetTrait Setters for
 * {@link \Firesphere\SearchBackend\Results\SearchResult}
 *
 * @package Firesphere\Elastic\Search
 * @author Simon `Firesphere` Erkelens; Marco `Sheepy` Hermo
 * @copyright Copyright (c) 2018 - now() Firesphere & Sheepy
 */

namespace Firesphere\SearchBackend\Traits;

use Firesphere\SearchBackend\Results\SearchResult;
use SilverStripe\View\ArrayData;

/**
 * Trait SearchResultSetTrait
 *
 * Getters for search results to keep the {@link SearchResult} class clean.
 *
 * @package Firesphere\Elastic\Search
 */
trait SearchResultSetTrait
{
    /**
     * @var int Total items in result
     */
    protected $totalItems;
    /**
     * @var ArrayData Facets
     */
    protected $facets;

    /**
     * @var Highlighting Highlighted items
     */
    protected $highlight;

    /**
     * Set the highlighted items
     *
     * @param $highlight
     * @return SearchResult
     */
    public function setHighlight($highlight): self
    {
        $this->highlight = $highlight;

        return $this;
    }

    /**
     * Set the total amount of results
     *
     * @param $count
     * @return self
     */
    public function setTotalItems($count): self
    {
        $this->totalItems = $count;

        return $this;
    }
}
