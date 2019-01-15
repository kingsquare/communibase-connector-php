<?php

namespace Communibase\Test;

/**
 * Class DebugStackTest
 *
 * @package Communibase\Test
 */
class DebugStackTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testDebugStackIsFilled()
    {
        $this->markTestIncomplete(
            'currently the logger is within the method which should be mocked for testing purposes; ' .
            'if the method is mocked then the logger will be empty!'
        );
    }
}
