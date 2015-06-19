<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://communibase.nl>.
 */

namespace Communibase;

/**
 * Class Exception
 *
 * @package Communibase
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 * @license	 http://opensource.org/licenses/MIT The MIT License (MIT)
 */
class Exception extends \Exception {

	/**
	 * a defined constant when the API is is invalid (or empty)
	 */
	const INVALID_API_KEY = 0;

	/**
	 * @var array
	 */
	private $errors;

	/**
	 * Overloaded to allow specific errors given by the API back to the handler
	 * @inherit
	 * @param null|string $message
	 * @param int $code
	 * @param Exception $previous
	 * @param array $errors
	 */
	public function __construct($message = null, $code = 0, Exception $previous = null, array $errors = array()) {
		$this->errors = $errors;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}
}