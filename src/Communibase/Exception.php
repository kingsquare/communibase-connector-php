<?php
namespace Communibase;

/**
 * Class Exception
 *
 * @package Communibase
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
class Exception extends \Exception
{
    /**
     * a defined constant when the API is is invalid (or empty)
     */
    const INVALID_API_KEY = 0;

    /**
     * @var array
     */
    private $errors;

    /**
     * Overloaded to allow specific errors given by the API back to the handler
     * @inherit
     *
     * @param null|string $message
     * @param int $code
     * @param \Exception $previous
     * @param array $errors
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null, array $errors = [])
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
