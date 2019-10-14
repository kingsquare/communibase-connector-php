<?php

namespace Communibase;

/**
 * @package Communibase
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class FinalizeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function provider()
    {
        return [
            ['Person', true],
            ['Invoice', false],
        ];
    }

    /**
     * @dataProvider provider
     *
     * @param string $entityType
     * @param string $expectException
     *
     * @throws Exception
     */
    public function testFinalizeCallIsPossibleForInvoiceOnly($entityType, $expectException)
    {
        /** @var Connector $stub */
        $stub = $this->getMockBuilder(Connector::class)
            ->setMethods(['doPost'])
            ->disableOriginalConstructor()
            ->getMock();

        if ($expectException) {
            $this->setExpectedException(Exception::class, 'Cannot call finalize on Person');
        }

        $stub->finalize($entityType, 'id');
    }
}
