<?php

namespace Communibase;

use Communibase\Logging\QueryLogger;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\StreamInterface;

/**
 * Communibase (https://communibase.nl) data Connector for PHP
 *
 * For more information see https://communibase.nl
 *
 * @package Communibase
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
class Connector implements ConnectorInterface
{
    /**
     * The official service URI; can be overridden via the constructor
     *
     * @var string
     */
    const SERVICE_PRODUCTION_URL = 'https://api.communibase.nl/0.1/';

    /**
     * The API key which is to be used for the api.
     * Is required to be set via the constructor.
     *
     * @var string
     */
    private $apiKey;

    /**
     * The url which is to be used for this connector. Defaults to the production url.
     * Can be set via the constructor.
     *
     * @var string
     */
    private $serviceUrl;

    /**
     * @var array of extra headers to send with each request
     */
    private $extraHeaders = [];

    /**
     * @var QueryLogger
     */
    private $logger;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * Create a new Communibase Connector instance based on the given api-key and possible serviceUrl
     *
     * @param string $apiKey The API key for Communibase
     * @param string $serviceUrl The Communibase API endpoint; defaults to self::SERVICE_PRODUCTION_URL
     * @param ClientInterface $client An optional GuzzleHttp Client (or Interface for mocking)
     */
    public function __construct(
        $apiKey,
        $serviceUrl = self::SERVICE_PRODUCTION_URL,
        ClientInterface $client = null
    ) {
        $this->apiKey = $apiKey;
        $this->serviceUrl = $serviceUrl;
        $this->client = $client;
    }

    /**
     * Returns an array that has all the fields according to the definition in Communibase.
     *
     * @param string $entityType
     *
     * @return array
     *
     * @throws Exception
     */
    public function getTemplate($entityType)
    {
        $params = [
            'fields' => 'attributes.title',
            'limit' => 1,
        ];
        $definition = $this->search('EntityType', ['title' => $entityType], $params);

        return array_fill_keys(array_merge(['_id'], array_column($definition[0]['attributes'], 'title')), null);
    }

    /**
     * Get a single Entity by its id
     *
     * @param string $entityType
     * @param string $id
     * @param array $params (optional)
     * @param string|null $version
     *
     * @return array entity
     *
     * @throws Exception
     */
    public function getById($entityType, $id, array $params = [], $version = null)
    {
        if (empty($id)) {
            throw new Exception('Id is empty');
        }

        if (!static::isIdValid($id)) {
            throw new Exception('Id is invalid, please use a correctly formatted id');
        }

        return ($version === null)
            ? $this->doGet($entityType . '.json/crud/' . $id, $params)
            : $this->doGet($entityType . '.json/history/' . $id . '/' . $version, $params);
    }

    /**
     * Get a single Entity by a ref-string
     *
     * @todo if the ref is a parent.parent.parent the code would need further improvement.
     *
     * @param array $ref
     * @param array $parentEntity (optional)
     *
     * @return array the referred Entity data
     *
     * @throws Exception
     */
    public function getByRef(array $ref, array $parentEntity = [])
    {
        if (empty($ref['rootDocumentEntityType']) && empty($ref['path'])) {
            throw new \InvalidArgumentException('Missing rootDocumentEntityType / path keys in $ref');
        }

        $document = $parentEntity;
        if (strpos($ref['rootDocumentEntityType'], 'parent') === false) {
            if (empty($document['_id']) || $document['_id'] !== $ref['rootDocumentId']) {
                $document = $this->getById($ref['rootDocumentEntityType'], $ref['rootDocumentId']);
            }

            if (count($document) === 0) {
                throw new Exception('Invalid document reference (document cannot be found by Id)');
            }
        }

        $container = $document;
        foreach ($ref['path'] as $pathInDocument) {
            if (!array_key_exists($pathInDocument['field'], $container)) {
                throw new Exception('Could not find the path in document');
            }
            $container = $container[$pathInDocument['field']];
            if (empty($pathInDocument['objectId'])) {
                continue;
            }

            if (!is_array($container)) {
                throw new Exception('Invalid value for path in document');
            }
            $result = array_filter($container, function ($item) use ($pathInDocument) {
                return $item['_id'] === $pathInDocument['objectId'];
            });
            if (count($result) === 0) {
                throw new Exception('Empty result of reference');
            }
            $container = reset($result);
        }
        return $container;
    }

    /**
     * Get an array of entities by their ids
     *
     * @param string $entityType
     * @param array $ids
     * @param array $params (optional)
     *
     * @return array entities
     *
     * @throws Exception
     */
    public function getByIds($entityType, array $ids, array $params = [])
    {
        $validIds = array_values(array_unique(array_filter($ids, [__CLASS__, 'isIdValid'])));

        if ($entityType === '' || count($validIds) === 0) {
            return [];
        }

        $doSortByIds = empty($params['sort']);
        $results = $this->search($entityType, ['_id' => ['$in' => $validIds]], $params);
        if (!$doSortByIds) {
            return $results;
        }

        $flipped = array_flip($validIds);
        foreach ($results as $result) {
            $flipped[$result['_id']] = $result;
        }
        return array_filter(array_values($flipped), function ($result) {
            return is_array($result) && count($result) > 0;
        });

    }

    /**
     * Get all entities of a certain type
     *
     * @param string $entityType
     * @param array $params (optional)
     *
     * @return array|null
     *
     * @throws Exception
     */
    public function getAll($entityType, array $params = [])
    {
        return $this->doGet($entityType . '.json/crud/', $params);
    }

    /**
     * Get result entityIds of a certain search
     *
     * @param string $entityType
     * @param array $selector (optional)
     * @param array $params (optional)
     *
     * @return array
     *
     * @throws Exception
     */
    public function getIds($entityType, array $selector = [], array $params = [])
    {
        $params['fields'] = '_id';

        return array_column($this->search($entityType, $selector, $params), '_id');
    }

    /**
     * Get the id of an entity based on a search
     *
     * @param string $entityType i.e. Person
     * @param array $selector (optional) i.e. ['firstName' => 'Henk']
     *
     * @return array resultData
     *
     * @throws Exception
     */
    public function getId($entityType, array $selector = [])
    {
        $params = ['limit' => 1];
        $ids = (array)$this->getIds($entityType, $selector, $params);

        return array_shift($ids);
    }

    /**
     * Call the aggregate endpoint with a given set of pipeline definitions:
     * E.g. [
     * { "$match": { "_id": {"$ObjectId": "52f8fb85fae15e6d0806e7c7"} } },
     * { "$unwind": "$participants" },
     * { "$group": { "_id": "$_id", "participantCount": { "$sum": 1 } } }
     * ]
     *
     * @see http://docs.mongodb.org/manual/core/aggregation-pipeline/
     *
     * @param $entityType
     * @param array $pipeline
     * @return array
     *
     * @throws Exception
     */
    public function aggregate($entityType, array $pipeline)
    {
        return $this->doPost($entityType . '.json/aggregate', [], $pipeline);
    }

    /**
     * Returns an array of the history for the entity with the following format:
     *
     * <code>
     *  [
     *        [
     *            'updatedBy' => '', // name of the user
     *            'updatedAt' => '', // a string according to the DateTime::ISO8601 format
     *            '_id' => '', // the ID of the entity which can ge fetched separately
     *        ],
     *        ...
     * ]
     * </code>
     *
     * @param string $entityType
     * @param string $id
     *
     * @return array
     *
     * @throws Exception
     */
    public function getHistory($entityType, $id)
    {
        return $this->doGet($entityType . '.json/history/' . $id);
    }

    /**
     * Search for the given entity by optional passed selector/params
     *
     * @param string $entityType
     * @param array $querySelector
     * @param array $params (optional)
     *
     * @return array
     *
     * @throws Exception
     */
    public function search($entityType, array $querySelector, array $params = [])
    {
        return $this->doPost($entityType . '.json/search', $params, $querySelector);
    }

    /**
     * This will save an entity in Communibase. When a _id-field is found, this entity will be updated
     *
     * NOTE: When updating, depending on the Entity, you may need to include all fields.
     *
     * @param string $entityType
     * @param array $properties - the to-be-saved entity data
     *
     * @return array resultData
     *
     * @throws Exception
     */
    public function update($entityType, array $properties)
    {
        if (empty($properties['_id'])) {
            return $this->doPost($entityType . '.json/crud/', [], $properties);
        }
        return $this->doPut($entityType . '.json/crud/' . $properties['_id'], [], $properties);
    }

    /**
     * Finalize an invoice by adding an invoiceNumber to it.
     * Besides, invoice items will receive a 'generalLedgerAccountNumber'.
     * This number will be unique and sequential within the 'daybook' of the invoice.
     *
     * NOTE: this is Invoice specific
     *
     * @param string $entityType
     * @param string $id
     *
     * @return array
     *
     * @throws Exception
     */
    public function finalize($entityType, $id)
    {
        if ($entityType !== 'Invoice') {
            throw new Exception('Cannot call finalize on ' . $entityType);
        }

        return $this->doPost($entityType . '.json/finalize/' . $id);
    }

    /**
     * Delete something from Communibase
     *
     * @param string $entityType
     * @param string $id
     *
     * @return array resultData
     *
     * @throws Exception
     */
    public function destroy($entityType, $id)
    {
        return $this->doDelete($entityType . '.json/crud/' . $id);
    }

    /**
     * Get the binary contents of a file by its ID
     *
     * NOTE: for meta-data like filesize and mimetype, one can use the getById()-method.
     *
     * @param string $id id string for the file-entity
     *
     * @return StreamInterface Binary contents of the file.
     * Since the stream can be made a string this works like a charm!
     *
     * @throws Exception
     */
    public function getBinary($id)
    {
        if (!static::isIdValid($id)) {
            throw new Exception('Invalid $id passed. Please provide one.');
        }

        return $this->call('get', ['File.json/binary/' . $id])->getBody();
    }

    /**
     * Uploads the contents of the resource (this could be a file handle) to Communibase
     *
     * @param StreamInterface $resource
     * @param string $name
     * @param string $destinationPath
     * @param string $id
     *
     * @return array|mixed
     *
     * @throws \RuntimeException | Exception
     */
    public function updateBinary(StreamInterface $resource, $name, $destinationPath, $id = '')
    {
        $metaData = ['path' => $destinationPath];
        if (!empty($id)) {
            if (!static::isIdValid($id)) {
                throw new Exception('Id is invalid, please use a correctly formatted id');
            }

            return $this->doPut('File.json/crud/' . $id, [], [
                'filename' => $name,
                'length' => $resource->getSize(),
                'uploadDate' => date('c'),
                'metadata' => $metaData,
                'content' => base64_encode($resource->getContents()),
            ]);
        }

        $options = [
            'multipart' => [
                [
                    'name' => 'File',
                    'filename' => $name,
                    'contents' => $resource
                ],
                [
                    'name' => 'metadata',
                    'contents' => json_encode($metaData),
                ]
            ]
        ];

        $response = $this->call('post', ['File.json/binary', $options]);

        return $this->parseResult($response->getBody(), $response->getStatusCode());
    }

    /**
     * Perform the actual GET
     *
     * @param string $path
     * @param array $params
     * @param array $data
     *
     * @return array
     *
     * @throws Exception
     */
    protected function doGet($path, array $params = null, array $data = null)
    {
        return $this->getResult('GET', $path, $params, $data);
    }

    /**
     * Perform the actual POST
     *
     * @param string $path
     * @param array $params
     * @param array $data
     *
     * @return array
     *
     * @throws Exception
     */
    protected function doPost($path, array $params = null, array $data = null)
    {
        return $this->getResult('POST', $path, $params, $data);
    }

    /**
     * Perform the actual PUT
     *
     * @param string $path
     * @param array $params
     * @param array $data
     *
     * @return array
     *
     * @throws Exception
     */
    protected function doPut($path, array $params = null, array $data = null)
    {
        return $this->getResult('PUT', $path, $params, $data);
    }

    /**
     * Perform the actual DELETE
     *
     * @param string $path
     * @param array $params
     * @param array $data
     *
     * @return array
     *
     * @throws Exception
     */
    protected function doDelete($path, array $params = null, array $data = null)
    {
        return $this->getResult('DELETE', $path, $params, $data);
    }

    /**
     * Process the request
     *
     * @param string $method
     * @param string $path
     * @param array $params
     * @param array $data
     *
     * @return array i.e. [success => true|false, [errors => ['message' => 'this is broken', ..]]]
     *
     * @throws Exception
     */
    protected function getResult($method, $path, array $params = null, array $data = null)
    {
        if (strpos($path, '.json') === 0) {
            throw new \InvalidArgumentException('Missing entitytype in request');
        }

        if ($params === null) {
            $params = [];
        }
        $options = [
            'query' => $this->preParseParams($params),
        ];
        if (is_array($data) && count($data) > 0) {
            $options['json'] = $data;
        }

        $response = $this->call($method, [$path, $options]);

        $responseData = $this->parseResult($response->getBody(), $response->getStatusCode());

        if ($response->getStatusCode() !== 200) {
            throw new Exception(
                $responseData['message'],
                $responseData['code'],
                null,
                (($_ =& $responseData['errors']) ?: [])
            );
        }

        return $responseData;
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    private function preParseParams(array $params)
    {
        if (!array_key_exists('fields', $params) || !is_array($params['fields'])) {
            return $params;
        }

        $fields = [];
        foreach ($params['fields'] as $index => $field) {
            if (!is_numeric($index)) {
                $fields[$index] = $field;
                continue;
            }

            $modifier = 1;
            /** @noinspection SubStrUsedAsArrayAccessInspection */
            $firstChar = substr($field, 0, 1);
            if ($firstChar === '+' || $firstChar === '-') {
                $modifier = $firstChar === '+' ? 1 : 0;
                $field = substr($field, 1);
            }
            $fields[$field] = $modifier;
        }
        $params['fields'] = $fields;

        return $params;
    }

    /**
     * Parse the Communibase result and if necessary throw an exception
     *
     * @param string $response
     * @param int $httpCode
     *
     * @return array
     *
     * @throws Exception
     */
    private function parseResult($response, $httpCode)
    {
        $result = json_decode($response, true);

        if (is_array($result)) {
            return $result;
        }

        throw new Exception('"' . $this->getLastJsonError() . '" in ' . $response, $httpCode);
    }

    /**
     * Error message based on the most recent JSON error.
     *
     * @see http://nl1.php.net/manual/en/function.json-last-error.php
     *
     * @return string
     */
    private function getLastJsonError()
    {
        static $messages = [
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        ];
        $jsonLastError = json_last_error();

        return array_key_exists($jsonLastError, $messages) ? $messages[$jsonLastError] : 'Empty response received';
    }

    /**
     * Verify the given $id is a valid Communibase string according to format
     *
     * @param string $id
     *
     * @return bool
     */
    public static function isIdValid($id)
    {
        if (empty($id)) {
            return false;
        }

        if (preg_match('#^[0-9a-fA-F]{24}$#', $id) === 0) {
            return false;
        }

        return true;
    }

    /**
     * Generate a Communibase compatible ID, that consists of:
     *
     * a 4-byte timestamp,
     * a 3-byte machine identifier,
     * a 2-byte process id, and
     * a 3-byte counter, starting with a random value.
     *
     * @return string
     */
    public static function generateId()
    {
        static $inc = 0;

        $ts = pack('N', time());
        $m = substr(md5(gethostname()), 0, 3);
        $pid = pack('n', 1);
        $trail = substr(pack('N', $inc++), 1, 3);

        $bin = sprintf('%s%s%s%s', $ts, $m, $pid, $trail);
        $id = '';
        for ($i = 0; $i < 12; $i++) {
            $id .= sprintf('%02X', ord($bin[$i]));
        }

        return strtolower($id);
    }

    /**
     * Add extra headers to be added to each request
     *
     * @see http://php.net/manual/en/function.header.php
     *
     * @param array $extraHeaders
     */
    public function addExtraHeaders(array $extraHeaders)
    {
        $this->extraHeaders = array_change_key_case($extraHeaders, CASE_LOWER);
    }

    /**
     * @param QueryLogger $logger
     */
    public function setQueryLogger(QueryLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return QueryLogger
     */
    public function getQueryLogger()
    {
        return $this->logger;
    }

    /**
     * @todo inroduce overloadability of default GuzzleClient settings
     * @return ClientInterface
     *
     * @throws Exception
     */
    protected function getClient()
    {
        if ($this->client instanceof ClientInterface) {
            return $this->client;
        }

        if (empty($this->apiKey)) {
            throw new Exception('Use of connector not possible without API key', Exception::INVALID_API_KEY);
        }

        $this->client = new Client([
            'connect_timeout' => 10,
            'base_uri' => $this->serviceUrl,
            'headers' => array_merge($this->extraHeaders, [
                'User-Agent' => 'Connector-PHP/2',
                'X-Api-Key' => $this->apiKey,
            ])
        ]);

        return $this->client;
    }

    /**
     * Perform the actual call to Communibase
     *
     * @param string $method
     * @param array $arguments
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws Exception
     */
    private function call($method, array $arguments)
    {
        try {

            /**
             * Due to GuzzleHttp not passing a default host header given to the client to _every_ request made by the
             * client we manually check to see if we need to add a hostheader to requests.
             * When the issue is resolved the foreach can be removed (as the function might even?)
             *
             * @see https://github.com/guzzle/guzzle/issues/1297
             */
            if (isset($this->extraHeaders['host'])) {
                $arguments[1]['headers']['Host'] = $this->extraHeaders['host'];
            }

            if ($this->logger) {
                $this->logger->startQuery($method . ' ' . reset($arguments), $arguments);
            }

            $response = call_user_func_array([$this->getClient(), $method], $arguments);

            if ($this->logger) {
                $this->logger->stopQuery();
            }

            return $response;

            // try to catch the Guzzle client exception (404's, validation errors etc) and wrap them into a CB exception
        } catch (ClientException $e) {

            $response = $e->getResponse();
            $response = json_decode($response === null ? '' : $response->getBody(), true);

            throw new Exception(
                $response['message'],
                $response['code'],
                $e,
                (($_ =& $response['errors']) ?: [])
            );

        } catch (ConnectException $e) {

            throw new Exception('Can not connect', 500, $e, []);

        }
    }
}
