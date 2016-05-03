<?php
namespace Communibase;

use Communibase\Logging\QueryLogger;
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
interface ConnectorInterface
{
    /**
     * Returns an array that has all the fields according to the definition in Communibase.
     *
     * @param string $entityType
     *
     * @return array
     *
     * @throws Exception
     */
    public function getTemplate($entityType);

    /**
     * Get a single Entity by its id
     *
     * @param string $entityType
     * @param string $id
     * @param array $params (optional)
     *
     * @return array entity
     *
     * @throws Exception
     */
    public function getById($entityType, $id, array $params = []);

    /**
     * Get a single Entity by a ref-string
     *
     * @param string $ref
     * @param array $parentEntity (optional)
     *
     * @return array the referred Entity data
     *
     * @throws Exception
     */
    public function getByRef($ref, array $parentEntity = []);

    /**
     * Get an array of entities by their ids
     *
     * @param string $entityType
     * @param array $ids
     * @param array $params (optional)
     *
     * @return array entities
     */
    public function getByIds($entityType, array $ids, array $params = []);

    /**
     * Get all entities of a certain type
     *
     * @param string $entityType
     * @param array $params (optional)
     *
     * @return array|null
     */
    public function getAll($entityType, array $params = []);

    /**
     * Get result entityIds of a certain search
     *
     * @param string $entityType
     * @param array $selector (optional)
     * @param array $params (optional)
     *
     * @return array
     */
    public function getIds($entityType, array $selector = [], array $params = []);

    /**
     * Get the id of an entity based on a search
     *
     * @param string $entityType i.e. Person
     * @param array $selector (optional) i.e. ['firstName' => 'Henk']
     *
     * @return array resultData
     */
    public function getId($entityType, array $selector = []);

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
     */
    public function aggregate($entityType, array $pipeline);

    /**
     * Returns an array of the history for the entity with the following format:
     *
     * <code>
     *  [
     *        [
     *            'updatedBy' => '', // name of the user
     *            'updatedAt' => '', // a string according to the DateTime::ISO8601 format
     *            '_id' => '', // the ID of the entity which can ge fetched seperately
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
    public function getHistory($entityType, $id);

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
    public function search($entityType, array $querySelector, array $params = []);

    /**
     * This will save an entity in Communibase. When a _id-field is found, this entity will be updated
     *
     * NOTE: When updating, depending on the Entity, you may need to include all fields.
     *
     * @param string $entityType
     * @param array $properties - the to-be-saved entity data
     *
     * @returns array resultData
     *
     * @throws Exception
     */
    public function update($entityType, array $properties);

    /**
     * Finalize an invoice by adding an invoiceNumber to it.
     * Besides, invoice items will receive a "generalLedgerAccountNumber".
     * This number will be unique and sequential within the "daybook" of the invoice.
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
    public function finalize($entityType, $id);

    /**
     * Delete something from Communibase
     *
     * @param string $entityType
     * @param string $id
     *
     * @return array resultData
     */
    public function destroy($entityType, $id);

    /**
     * Get the binary contents of a file by its ID
     *
     * NOTE: for meta-data like filesize and mimetype, one can use the getById()-method.
     *
     * @param string $id id string for the file-entity
     *
     * @return StreamInterface Binary contents of the file. Since the stream can be made a string this works like a charm!
     *
     * @throws Exception
     */
    public function getBinary($id);

    /**
     * Uploads the contents of the resource (this could be a file handle) to Communibase
     *
     * @param StreamInterface $resource
     * @param string $name
     * @param string $destinationPath
     * @param string $id
     *
     * @return array|mixed
     * @throws Exception
     */
    public function updateBinary(StreamInterface $resource, $name, $destinationPath, $id = '');

    /**
     * Add extra headers to be added to each request
     *
     * @see http://php.net/manual/en/function.header.php
     *
     * @param array $extraHeaders
     */
    public function addExtraHeaders(array $extraHeaders);

    /**
     * @param QueryLogger $logger
     */
    public function setQueryLogger(QueryLogger $logger);

    /**
     * @return QueryLogger
     */
    public function getQueryLogger();
}
