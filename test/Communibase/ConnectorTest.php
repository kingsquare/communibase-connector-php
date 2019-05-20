<?php

namespace Communibase;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

/**
 * Class ConnectorTest
 *
 * @todo still alot more tests should be added, these are a first start. Feel free to submit new/better tests!
 * @package Communibase
 */
class ConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function constructor()
    {
        new Connector('', '');
    }

    /**
     * @test
     */
    public function constructorWithClient()
    {
        $connector = new Connector('test', '', $this->getHttpClient());
        $this->assertInstanceOf(Connector::class, $connector);
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function getBinary()
    {
        $connector = new Connector('test', '');
        $connector->getBinary('');
    }

    /**
     * @return array
     */
    public function isIdProvider()
    {
        return [
            ['507f1f77bcf86cd799439011', true],
            ['507f191e810c19729de860ea', true],
            ['54b7ed2b49726734cab0570c', true],
            ['123c', false],
            ['t', false],
            ['t', false],
            ['58a2d90012f9ae00c647d0fc((\'.,.', false],
        ];
    }

    /**
     * @dataProvider isIdProvider
     * @test
     * @param $id
     * @param $isValid
     */
    public function isIdValid($id, $isValid)
    {
        $this->assertSame($isValid, Connector::isIdValid($id));
    }

    /**
     * @test
     * @depends isIdValid
     */
    public function generateId()
    {
        $id = Connector::generateId();

        $this->assertTrue(Connector::isIdValid($id));
    }

    /**
     * Create a new HttpClient (as a mock)
     * The responses can be injected thus easily reusable
     *
     * @see http://docs.guzzlephp.org/en/latest/testing.html
     *
     * @param array $responses
     *
     * @return Client
     */
    private function getHttpClient(array $responses = [])
    {

        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);

        return new Client(['handler' => $handler]);
    }
}
