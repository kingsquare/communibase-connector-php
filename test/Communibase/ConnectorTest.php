<?php
namespace Communibase;

/**
 * Class ConnectorTest
 *
 * @todo still alot more tests should be added, these are a first start. Feel free to submit new/better tests!
 * @package Communibase
 */
class ConnectorTest extends \PHPUnit_Framework_TestCase
{

    public function testGenerateIdIsValid()
    {
        $connector = new \Communibase\Connector('', '');
        $id = $connector->generateId();

        $this->assertRegExp('#[0-9a-f]{24}#', $id);
    }

    /**
     * @expectedException Exception
     */
    public function testGetBinaryWithoutApiThrowsException()
    {
        $connector = new \Communibase\Connector('', '');
        $connector->getBinary('');
    }
}
