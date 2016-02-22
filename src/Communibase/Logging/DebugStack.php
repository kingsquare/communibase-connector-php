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
    public $start = null;

    /**
     * @var integer
     */
    public $currentQuery = 0;

    /**
     * {@inheritdoc}
     */
    public function startQuery($query, array $params = null, array $data = null)
    {
        if ($this->enabled) {
            $this->start = microtime(true);
            $this->queries[++$this->currentQuery] = [
                    'query' => $query,
                    'params' => $params,
                    'data' => $data,
                    'executionMS' => 0
            ];
        }
        return $this->currentQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery($idx = null)
    {
        if ($this->enabled) {
            $this->queries[$idx !== null ? $idx : $this->currentQuery]['executionMS'] = microtime(true) - $this->start;
        }
    }
}
