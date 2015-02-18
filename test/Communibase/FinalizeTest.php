<?php
namespace Communibase;

/**
 * @package Communibase
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class FinalizeTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @TODO Test schould also check the reversed.
	 * @expectedException Exception
	 * @expectedExceptionMessage Cannot call finalize on Person
	 */
	public function testFinalizeCallIsPossibleForInvoiceOnly() {
		$stub = $this->getMockBuilder('Communibase\Connector')
				->setMethods(null)
				->disableOriginalConstructor()
				->getMock();

		// must throw exception
		$stub->finalize('Person', 'id');
	}

}
