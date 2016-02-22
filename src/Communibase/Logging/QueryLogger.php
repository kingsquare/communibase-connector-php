<?php
namespace Communibase\Logging;

/**
 * Interface for Query loggers.
 */
interface QueryLogger
{
    /**
     * Logs a Query somewhere.
     *
     * @param string $query The Query to be executed.
     * @param array|null $params The Query parameters.
     * @param array|null $data The Query data/payload.
     *
     * @return int The query index
     */
    public function startQuery($query, array $params = null, array $data = null);

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @param integer $idx The query index
     *
     * @return void
     */
    public function stopQuery($idx = null);
}
