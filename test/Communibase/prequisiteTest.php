<?php

/**
 * Class PrequisiteTest
 *
 * @package Communibase
 */
class PrequisiteTest extends \PHPUnit_Framework_TestCase {

	/**
	 *
	 */
	public function testPrequisites() {
		$this->assertTrue(function_exists('array_column'),
				'Missing array_column, thus composer needs to be install if PHP < 5.5');
	}

}