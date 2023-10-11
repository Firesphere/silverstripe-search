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
     * @return $this
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
     * @return $this
     */
    public function setRows($rows): self
    {
        $this->rows = $rows;

        return $this;
    }
}
