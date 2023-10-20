<?php

namespace Firesphere\SearchBackend\Interfaces;

use Firesphere\SearchBackend\Indexes\CoreIndex;
use Firesphere\SearchBackend\Queries\CoreQuery;

interface QueryBuilderInterface
{
    /**
     * @param CoreQuery $query
     * @param CoreIndex $index
     * @return mixed
     */
    public static function buildQuery(CoreQuery $query, CoreIndex $index);
}
