<?php

namespace Communibase;

/**
 * Class ExceptionTest
 *
 * @package Communibase
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function givenErrorsAreReturned()
    {
        $e = new Exception('message', 0, null, ['test']);
        $this->assertInstanceOf(Exception::class, $e);
        $this->assertSame('message', $e->getMessage());
        $this->assertSame(0, $e->getCode());
        $this->assertSame(['test'], $e->getErrors());
    }
}