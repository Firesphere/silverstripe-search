<?php

namespace Firesphere\SearchBackend\Interfaces;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\PaginatedList;
use Solarium\Component\Result\FacetSet;
use stdClass;

interface SearchResultInterface
{
    /**
     * Get the matches as an ArrayList and add an excerpt if possible.
     * {@link static::createExcerpt()}
     *
     * @return ArrayList
     */
    public function getMatches(): ArrayList;

    /**
     * Paginated version of getPaginatedMatches
     * @return PaginatedList
     */
    public function getPaginatedMatches(): PaginatedList;

    /**
     * Get the highlights for a specific document
     * @param string|int $docId ID of the document to search for
     * @return string
     */
    public function getHighlightByID($docId): string;

    /**
     * @return mixed
     */
    public function getSpellcheck(): ArrayList;

    /**
     * Create a single facet array for a faceted class
     * @param stdClass|FacetSet $facets
     * @param array|stdClass $options
     * @param string $class
     * @param array $facetArray
     * @return array
     */
    public function createFacet($facets, $options, $class, array $facetArray): array;
}
