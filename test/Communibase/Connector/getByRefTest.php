<?php
namespace Communibase;

/**
 * @package Communibase
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class GetByRefTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @expectedException Exception
     * @expectedExceptionMessage Please provide a documentReference object with a type and id
     */
    public function invalid()
    {
        $this->getMockConnector()->getByRef([]);
    }

    /**
     * @test
     */
    public function singlePathToAttribute()
    {

        $result = $this->getMockConnector()->getByRef([
            'rootDocumentEntityType' => 'Test',
            'rootDocumentId' => 'X',
            'path' => [
                [
                    'field' => 'singlePathToAttribute'
                ]
            ]
        ]);

        $this->assertSame('value', $result);
    }

    /**
     * @test
     */
    public function multiplePathToAttribute()
    {

        $result = $this->getMockConnector()->getByRef([
            'rootDocumentEntityType' => 'Test',
            'rootDocumentId' => 'X',
            'path' => [
                [
                    'field' => 'multiplePathToAttribute'
                ],
                [
                    'field' => 'field'
                ]
            ]
        ]);

        $this->assertSame('value', $result);
    }

    /**
     * @test
     */
    public function multiplePathToAttributeDepth()
    {

        $result = $this->getMockConnector()->getByRef([
            'rootDocumentEntityType' => 'Test',
            'rootDocumentId' => 'X',
            'path' => [
                [
                    'field' => 'multiplePathToAttributeDepth'
                ],
                [
                    'field' => 'field'
                ],
                [
                    'field' => 'field'
                ]
            ]
        ]);

        $this->assertSame('value', $result);
    }

    /**
     * @test
     */
    public function singlePathToArrayObject()
    {

        $result = $this->getMockConnector()->getByRef([
            'rootDocumentEntityType' => 'Test',
            'rootDocumentId' => 'X',
            'path' => [
                [
                    'field' => 'singlePathToArrayObject',
                    'objectId' => 'value'
                ]
            ]
        ]);

        $this->assertSame(['_id' => 'value'], $result);
    }

    /**
     * @test
     */
    public function multiplePathToArrayObject()
    {

        $result = $this->getMockConnector()->getByRef([
            'rootDocumentEntityType' => 'Test',
            'rootDocumentId' => 'X',
            'path' => [
                [
                    'field' => 'multiplePathToArrayObject',
                ],
                [
                    'field' => 'field',
                    'objectId' => 'value'
                ]
            ]
        ]);

        $this->assertSame(['_id' => 'value'], $result);
    }

    /**
     * @return \Communibase\Connector
     */
    protected function getMockConnector() {

        $mock = $this->getMockBuilder('Communibase\Connector')
            ->setMethods(['getById'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getById')
            ->will($this->returnValue([
                '_id' => '1234',
                'singlePathToAttribute' => 'value',
                'multiplePathToAttribute' => [
                    'field' => 'value'
                ],
                'multiplePathToAttributeDepth' => [
                    'field' => [
                        'field' => 'value'
                    ],
                ],
                'singlePathToArrayObject' => [
                    [
                        '_id' => 'value'
                    ]
                ],
                'multiplePathToArrayObject' => [
                    'field' => [
                        [
                            '_id' => 'value'
                        ]
                    ]
                ],
                'multiplePathToArrayObjectDepth' => [
                    'field' => [
                        'field' => [
                            [
                                '_id' => 'value'
                            ]
                        ]
                    ]
                ],
            ]));

        return $mock;
    }

}
