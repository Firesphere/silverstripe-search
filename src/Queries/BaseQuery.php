<?php

namespace Firesphere\SearchBackend\Queries;

abstract class BaseQuery
{
    /**
     * @var int Pagination start
     */
    protected $start = 0;
    /**
     * @var int Total rows to display
     */
    protected $rows = 10;
    /**
     * @var array Sorting settings
     */
    protected $sort = [];
    /**
     * @var array Filters to use/apply
     */
    protected $filters = [];

    /**
     * @var array Filters that are not exclusive
     */
    protected $orFilters = [];
    /**
     * @var array Search terms
     */
    protected $terms = [];
    /**
     * @var array
     */
    protected $boostedFields = [];
    /**
     * @var bool
     */
    protected $highlight = true;
    /**
     * @var bool Enable spellchecking?
     */
    protected $spellcheck = true;

    /**
     * Get the search terms
     *
     * @return array
     */
    public function getTerms(): array
    {
        return $this->terms;
    }

    /**
     * Set the search tearms
     *
     * @param array $terms
     * @return $this
     */
    public function setTerms($terms): self
    {
        $this->terms = $terms;

        return $this;
    }

    /**
     * Each boosted query needs a separate addition!
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
     * @param bool|float|null $fuzzy True or a value to the maximum amount of iterations
     * @return $this
     * @todo fix this to not be Solr~ish
     * For generic boosting, use @addBoostedField($field, $boost), this will add the boost at Index time
     *
     */
    abstract public function addTerm(string $term, array $fields = [], int $boost = 1, $fuzzy = null): self;

    /**
     * @param string $key Field to apply filter on
     * @param string|array $value Value(s) to filter on
     * @return BaseQuery
     */
    public function addFilter($key, $value): BaseQuery
    {
        $this->filters[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param array $filters
     * @return BaseQuery
     */
    public function setFilters(array $filters): BaseQuery
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Get the OR filters for this query
     *
     * @return array
     */
    public function getOrFilters(): array
    {
        return $this->orFilters;
    }

    /**
     * Set the or filters for this query
     * @param array $orFilters
     * @return self
     */
    public function setOrFilters(array $orFilters): self
    {
        $this->orFilters = $orFilters;

        return $this;
    }

    /**
     * Add the or filters in a key-value pair
     * @param string $key
     * @param string $orFilters
     * @return self
     */
    public function addOrFilters(string $key, string $orFilters): self
    {
        $this->orFilters[$key] = $orFilters;

        return $this;
    }

    /**
     * Get the offset to start
     *
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * Set the offset to start
     *
     * @param int $start
     * @return self
     */
    public function setStart($start): self
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get the rows to return
     *
     * @return int
     */
    public function getRows(): int
    {
        return $this->rows;
    }

    /**
     * Set the rows to return
     *
     * @param int $rows
     * @return self
     */
    public function setRows($rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * Get the sort fields
     *
     * @return array
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * Set the sort fields
     *
     * @param array $sort
     * @return self
     */
    public function setSort($sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function getBoostedFields(): array
    {
        return $this->boostedFields;
    }

    public function setBoostedFields(array $boostedFields): void
    {
        $this->boostedFields = $boostedFields;
    }

    public function addBoostedField($key, $value): self
    {
        $this->boostedFields[$key] = $value;

        return $this;
    }

    public function isHighlight(): bool
    {
        return $this->highlight;
    }

    public function setHighlight(bool $highlight): self
    {
        $this->highlight = $highlight;

        return $this;
    }
}
