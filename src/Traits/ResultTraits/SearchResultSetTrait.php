<?php
/**
 * Trait SearchResultSetTrait|Firesphere\SearchBackend\Traits\SearchResultSetTrait Setters for
 * {@link \Firesphere\SearchBackend\Interfaces\SearchResultInterface}
 *
 * @package Firesphere\Elastic\Search
 * @author Simon `Firesphere` Erkelens; Marco `Sheepy` Hermo
 * @copyright Copyright (c) 2018 - now() Firesphere & Sheepy
 */

namespace Firesphere\SearchBackend\Traits;

use Firesphere\SearchBackend\Interfaces\SearchResultInterface;
use SilverStripe\View\ArrayData;
use Solarium\Component\Result\FacetSet;
use stdClass;

/**
 * Trait SearchResultSetTrait
 *
 * Getters for search results to keep the {@link SearchResultInterface} class clean.
 *
 * @package Firesphere\Elastic\Search
 */
trait SearchResultSetTrait
{
    /**
     * @var int Total items in result
     */
    protected $totalItems = 0;
    /**
     * @var ArrayData Facets
     */
    protected $facets;

    /**
     * @var array|stdClass[] Highlighted items
     */
    protected $highlight;

    /**
     * Set the highlighted items
     *
     * @param $highlight
     * @return SearchResultInterface
     */
    public function setHighlight($highlights): SearchResultInterface
    {
        $this->highlight = $highlights;

        return $this;
    }

    /**
     * Set the total amount of results
     *
     * @param $count
     * @return self
     */
    public function setTotalItems($count): SearchResultInterface
    {
        $this->totalItems = $count;

        return $this;
    }


    /**
     * Set the facets to build
     *
     * @param FacetSet|null $facets
     * @return self
     */
    public function setFacets($facets): self
    {
        $this->facets = $this->buildFacets($facets);

        return $this;
    }


    /**
     * Build the given list of key-value pairs in to a SilverStripe useable array
     *
     * @param FacetSet|null $facets
     * @return ArrayData
     */
    protected function buildFacets($facets): ArrayData
    {
        $facetArray = [];
        if ($facets) {
            $facetTypes = $this->index->getFacetFields();
            // Loop all available facet fields by type
            foreach ($facetTypes as $class => $options) {
                $facetArray = $this->createFacet($facets, $options, $class, $facetArray);
            }
        }

        // Return an ArrayList of the results
        return ArrayData::create($facetArray);
    }
}
