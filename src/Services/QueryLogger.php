<?php

namespace Firevel\RequestLogger\Services;

class QueryLogger
{
    /**
     * Queries executed.
     *
     * @var array
     */
    protected $queries = [];

    /**
     * Listen to queries
     * @return void
     */
    public function listen()
    {
        \DB::listen(fn($query) => $this->addQuery($query));
    }

    /**
     * Add query.
     *
     * @param array $query
     */
    public function addQuery($query)
    {
        $this->queries[] = ['sql' => $query->sql, 'time' => $query->time];
    }

    /**
     * List of queries executed.
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->queries;
    }
}
