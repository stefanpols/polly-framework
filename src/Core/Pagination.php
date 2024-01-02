<?php

namespace Polly\Core;


use ArrayObject;
use Polly\Helpers\Str;

class Pagination
{
    private array $results;
    private int $resultsPerPage = 0;
    private int $currentPage = 0;
    private int $totalCount = 0;
    private bool $enabled = true;

    public function __construct()
    {}

    public function getOffset() : int
    {
        return ($this->currentPage-1) * ($this->resultsPerPage);
    }

    public function getResultStart() : int
    {
        if(empty($this->getResults())) return 0;
        return $this->getOffset() + 1;
    }

    public function getResultEnd() : int
    {
        if(empty($this->getResults())) return 0;
        return $this->getResultStart() + (count($this->getPageResults())-1);
    }

    public function getIdList() : array
    {
       return array_keys($this->getResults());
    }

    public function getTotalCount()
    {
        return count($this->getResults());
    }

    public function &getPageResults()
    {
        if(!$this->isEnabled()) return $this->getResults();
        $array_slice = array_slice($this->getResults(), $this->getOffset(), $this->getResultsPerPage());
        return $array_slice;
    }
    public function getTotalPages()
    {
        if($this->getResultsPerPage() == 0) return 0;
        return ceil(($this->getTotalCount()+1) / $this->getResultsPerPage());
    }

    public function getPrevPage()
    {
        $page = $this->getCurrentPage();
        if($page > 1)
            $page--;

        return $page;
    }

    public function getNextPage()
    {
        $page = $this->getCurrentPage();
        if($page < $this->getTotalPages())
            $page++;

        return $page;
    }

    /**
     * @return array
     */
    public function &getResults(): array
    {
        return $this->results;
    }

    /**
     * @param array $results
     */
    public function setResults(array $results): void
    {
        $this->results = $results;
    }


    /**
     * @return int
     */
    public function getResultsPerPage(): int
    {
        return $this->resultsPerPage;
    }

    /**
     * @param int $resultsPerPage
     */
    public function setResultsPerPage(int $resultsPerPage): void
    {
        $this->resultsPerPage = $resultsPerPage;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage(int $currentPage): void
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }






}
