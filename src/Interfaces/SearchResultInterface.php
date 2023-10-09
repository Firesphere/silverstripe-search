<?php

namespace Firesphere\SearchBackend\Interfaces;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\View\ViewableData;

interface SearchResultInterface
{

    /**
     * Get the matches as an ArrayList and add an excerpt if possible.
     * {@link static::createExcerpt()}
     *
     * @return ArrayList
     */
    public function getMatches(): ArrayList;

    public function getPaginatedMatches(): PaginatedList;


}