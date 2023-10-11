<?php

namespace Firesphere\SearchBackend\Interfaces;

use Firesphere\SearchBackend\Indexes\CoreIndex;
use Firesphere\SearchBackend\Queries\BaseQuery;

interface QueryBuilderInterface
{
    /**
     * @param BaseQuery $query
     * @param CoreIndex $index
     * @return mixed
     */
    public static function buildQuery(BaseQuery $query, CoreIndex $index);
}
