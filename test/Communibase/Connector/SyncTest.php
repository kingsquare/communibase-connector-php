<?php
namespace Communibase;

use GuzzleHttp\Promise\FulfilledPromise;

/**
 * superfluous?
 *
 * @package Communibase
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class SyncTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function sync()
    {
        $result = $this->getMockConnector()->getByIdSync('Person', $this->getMockConnector()->generateId());
        static::assertSame([], $result);
    }

    /**
     * @test
     */
    public function async()
    {
        $this->getMockConnector()->getById('Person', $this->getMockConnector()->generateId())->then(function ($result) {
            static::assertSame([], $result);
        })->wait(); // wait to complete the test ;-)
    }

    /**
     * @return \Communibase\Connector
     */
    protected function getMockConnector()
    {
        $mock = $this->getMockBuilder('Communibase\Connector')
                     ->setMethods(['getResult'])
                     ->disableOriginalConstructor()
                     ->getMock();

        $mock->expects(static::any())
             ->method('getResult')
             ->will(static::returnValue(new FulfilledPromise([])));

        return $mock;
    }
}