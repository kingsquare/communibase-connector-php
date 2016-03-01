<?php
namespace Communibase\Logging;

/**
 * A Query logger that logs to the standard output using echo/var_dump.
 */
class EchoQueryLogger implements QueryLogger
{
    /**
     * {@inheritdoc}
     */
    public function startQuery($query, array $params = null, array $data = null)
    {
        echo $query . PHP_EOL;

        if ($params) {
            var_dump($params);
        }

        if ($data) {
            var_dump($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery($idx = null)
    {
    }
}
