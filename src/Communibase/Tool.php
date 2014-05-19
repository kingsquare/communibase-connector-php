<?php
namespace Communibase;

/**
 * Class Tool
 *
 * @package Communibase
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 * @license	 http://opensource.org/licenses/MIT The MIT License (MIT)
 */
class Tool {

	/**
	 * @param string $mongoId
	 * @return \Datetime
	 */
	public static function dateFromMongoId($mongoId) {
		return new \DateTime('@'.hexdec(substr($mongoId, 0, 8)));
	}
}
