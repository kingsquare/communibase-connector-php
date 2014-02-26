<?php
namespace Communibase;

/**
 * Class Connector
 *
 * @package Communibase
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 * @license	 http://opensource.org/licenses/MIT The MIT License (MIT)
 */
class Connector {

	/**
	 * @var string - ugly, but overrulable for e.g. a local, non-validating instance
	 */
	const SERVICE_PRODUCTION_URL = 'https://api.communibase.nl/0.1/';

	/**
	 * The url which is to be used for this connector. Defaults to the production url
	 *
	 * @var string
	 */
	private $serviceUrl;

	/**
	 * The API key which is to be used for the api
	 * @var string
	 */
	private $apiKey;

	/**
	 * @param $apiKey $apiKey The API key for Communibase
	 * @param string $serviceUrl
	 */
	function __construct($apiKey, $serviceUrl = self::SERVICE_PRODUCTION_URL) {
		$this->serviceUrl = $serviceUrl;
		$this->apiKey = $apiKey;
	}

	/**
	 * Get a single object by its id
	 *
	 * @param string $objectType
	 * @param string $objectId
	 * @param array $params (optional)
	 * @return array document
	 */
	function getById($objectType, $objectId, $params = array()) {
		$ch = $this->setupCurlHandle($objectType . '.json/crud/' . $objectId, $params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		return $this->getResult($ch);
	}

	/**
	 * Get a single object by a ref-string
	 *
	 * @param string $ref
	 * @param array $parentDocument (optional)
	 * @throws \Communibase\Exception
	 *
	 * @return array the referred object data
	 */
	function getByRef($ref, $parentDocument = array()) {
		$refParts = explode('.', $ref);
		if ($refParts[0] !== 'parent') {
			$entityParts = explode('|', $refParts[0]);
			$parentDocument = $this->getById($entityParts[0], $entityParts[1]);
		}
		if (empty($refParts[1])) {
			return $parentDocument;
		}
		$propertyParts = explode('|', $refParts[1]);
		foreach($parentDocument[$propertyParts[0]] as $subDocument) {
			if ($subDocument['_id'] === $propertyParts[1]) {
				return $subDocument;
			}
		}
		throw new \Communibase\Exception('Could not find the referred object');
	}

	/**
	 * Get an array of objects by their ids
	 *
	 * @param string $objectType
	 * @param array $objectIds
	 * @param array $params (optional)
	 * @return array documents
	 */
	function getByIds($objectType, $objectIds, $params = array()) {
		if (empty($objectIds)) {
			return array();
		}
		return $this->search($objectType, array('_id' => array('$in' => $objectIds)), $params);
	}

	/**
	 * Get all objects of a certain type
	 *
	 * @param string $objectType
	 * @param array $params (optional)
	 * @return array|null
	 */
	function getAll($objectType, $params = array()) {
		$ch = $this->setupCurlHandle($objectType . '.json/crud/', $params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		return $this->getResult($ch);
	}

	/**
	 * Get result objectIds of a certain search
	 *
	 * @param string $objectType
	 * @param array $selector (optional)
	 * @param array $params (optional)
	 * @return array
	 */
	function getIds($objectType, $selector = array(), $params = array()) {
		$params['fields'] = '_id';
		$objectIds = array_map(
			function ($object) {
				return $object['_id'];
			},
			$this->search($objectType, $selector, $params)
		);
		return $objectIds;
	}

	/**
	 * Get the id of an object based on a search
	 *
	 * @param string $objectType i.e. Person
	 * @param array $selector (optional) i.e. ['firstName' => 'Henk']
	 * @return array resultData
	 */
	function getId($objectType, $selector = array()) {
		return array_shift($this->getIds($objectType, $selector, array('limit' => 1)));
	}

	/**
	 * @param string $entityType
	 * @param string $querySelector
	 * @param array $params (optional)
	 * @return array
	 */
	function search($entityType, $querySelector, $params = array()) {
		$url = $entityType . '.json/search';
		$ch = $this->setupCurlHandle($url, $params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($querySelector));
		return $this->getResult($ch);
	}

	/**
	 * This will save a document in Communibase. When a _id-field is found, this document will be updated
	 *
	 * @param string $objectType
	 * @param array $properties - the to-be-saved object data
	 * @returns array resultData
	 */
	function update($objectType, $properties) {
		$isNew = empty($properties['_id']);
		$ch = $this->setupCurlHandle($objectType . '.json/crud/' . ($isNew ? '' : $properties['_id']));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, ($isNew ? 'POST' : 'PUT'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($properties));
		return $this->getResult($ch);
	}

	/**
	 * Delete something from Communibase
	 *
	 * @param string $objectType
	 * @param string $objectId
	 * @returns array resultData
	 */
	function destroy($objectType, $objectId) {
		$ch = $this->setupCurlHandle($objectType . '.json/crud/' . $objectId);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		return $this->getResult($ch);
	}


	/**
	 * Generate a MongoDB compatible ID, that consist of :
	 *
	 * a 4-byte timestamp,
	 * a 3-byte machine identifier,
	 * a 2-byte process id, and
	 * a 3-byte counter, starting with a random value.
	 */
	public function generateId( ) {
		static $inc = 0;

		$ts = pack('N', time());
		$m = substr(md5(gethostname()), 0, 3);
		$pid = pack('n', 1); //posix_getpid()
		$trail = substr(pack('N', $inc++), 1, 3);

		$bin = sprintf("%s%s%s%s", $ts, $m, $pid, $trail);
		$id = '';
		for ($i = 0; $i < 12; $i++) {
			$id .= sprintf("%02X", ord($bin[$i]));
		}
		return strtolower($id);
	}

	/**
	 * Setup a curl handle for Communibase Requests
	 *
	 * @param string $url
	 * @param array $params (optional)
	 * @throws \Communibase\Exception
	 * @return resource
	 */
	private function setupCurlHandle($url, $params = array()) {
		if (empty($this->apiKey)) {
			throw new \Communibase\Exception('Use of connector not possible without API key', Exception::INVALID_API_KEY);
		}
		$params['api_key'] = $this->apiKey;
		$ch = curl_init($this->serviceUrl . $url . '?' . http_build_query($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		return $ch;
	}

	/**
	 * Process the curl handle to a response
	 *
	 * @param resource $ch - Curl handle
	 * @param bool $expectResponseStatus200 - throw an error when the response status !== 200
	 * @throws \Communibase\Exception
	 * @return array i.e. [success => true|false, [errors => ['message' => 'this is broken', ..]]]
	 */
	private function getResult($ch, $expectResponseStatus200 = true) {
		$response = curl_exec($ch);
		if ($expectResponseStatus200) {
			$responseData = curl_getinfo($ch);
			if ($responseData['http_code'] !== 200) {
				$response = json_decode($response, true);
				throw new \Communibase\Exception($response['message'],
						$response['code'],
						null,
						(($_=& $response['errors']) ?: array()));
			}
		}
		curl_close($ch);

		if ($response === false) {
			throw new \Communibase\Exception('Curl failed. ' . PHP_EOL . curl_error($ch));
		}

		$result = json_decode($response, true);
		if (!is_array($result)) {
			throw new \Communibase\Exception('Communibase failed. ' . PHP_EOL . $response);
		}
		return $result;
	}

	/**
	 * Get the binary contents of a file by its ID
	 *
	 * NOTE: for meta-data like filesize and mimetype, one can use the getById()-method.
	 *
	 * @param string $objectId mongo ObjectID string for the file
	 * @throws \Communibase\Exception
	 * @return string Binary contents of the file.
	 */
	public function getBinary($objectId) {
		if (empty($this->apiKey)) {
			throw new \Communibase\Exception('Use of connector not possible without API key', Exception::INVALID_API_KEY);
		}
		return file_get_contents($this->serviceUrl . 'File.json/binary/' . $objectId . '?api_key=' . $this->apiKey);
	}
}