<?php

namespace Firesphere\SearchBackend\Interfaces;

interface QueryInterface
{

    public function getTerms(): array;

    public function setTerms($terms): self;

    /**
     * Add a term to search on.
     * Note, each boosted query needs a separate addition!
     * e.g. $this->addTerm('test', ['MyField', 'MyOtherField'], 3)
     * followed by
     * $this->addTerm('otherTest', ['Title'], 5);
     *
     * If you want a generic boost on all terms, use addTerm only once, but boost on each field
     *
     * The fields parameter is used to boost on
     * @param string $term Term to search for
     * @param array $fields fields to boost on
     * @param int $boost Boost value
     * @param float|bool|null $fuzzy True or a value to the maximum amount of iterations
     * @return $this
     */
    public function addTerm(string $term, array $fields = [], int $boost = 1, float|bool $fuzzy = null): self;

    public function getFilters(): array;
    public function setFilters(array $filters): self;

    public function addFilter(string $key, string $value): self;

    public function getOrFilters(): array;

    public function setOrFilters(array $filters): self;

    public function addOrFilter(string $key, string $value): self;
}
