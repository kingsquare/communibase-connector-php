<?php

namespace Communibase\Logging;

/**
 * Includes executed Queries in a Debug Stack.
 */
class DebugStack implements QueryLogger
{
    /**
     * Executed queries.
     *
     * @var array
     */
    public $queries = [];

    /**
     * If Debug Stack is enabled (log queries) or not.
     *
     * @var boolean
     */
    public $enabled = true;

    /**
     * @var float|null
     */
    public $start;

    /**
     * @var integer
     */
    public $currentQuery = 0;

    /**
     * {@inheritdoc}
     */
    public function startQuery($query, array $params = null, array $data = null)
    {
        if (!$this->enabled) {
            return;
        }
        $this->start = microtime(true);
        $this->queries[++$this->currentQuery] = [
            'query' => $query,
            'params' => $params,
            'data' => $data,
            'executionMS' => 0
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        if (!$this->enabled) {
            return;
        }
        $this->queries[$this->currentQuery]['executionMS'] = microtime(true) - $this->start;
    }
}
