[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/kingsquare/communibase-connector-php/badges/quality-score.png?s=94ea144a5b63afdb4ff9b99991f5ca830ba59d37)](https://scrutinizer-ci.com/g/kingsquare/communibase-connector-php/)
[![Travis CI](https://travis-ci.org/kingsquare/communibase-connector-php.svg)](https://travis-ci.org/kingsquare/communibase-connector-php)
[![Latest Stable Version](https://poser.pugx.org/kingsquare/communibase-connector-php/v/stable.png)](https://packagist.org/packages/kingsquare/communibase-connector-php)
[![License](https://poser.pugx.org/kingsquare/communibase-connector-php/license.png)](https://packagist.org/packages/kingsquare/communibase-connector-php)

A general-purpose [Communibase](https://www.communibase.nl) PHP client.

A connector may be constructed to do REST-calls on the Communibase API.  The behaviour of this class should always Mimic
the node.js-version, available at [Github](https://github.com/kingsquare/communibase-connector-js)

# Installation

    composer require kingsquare/communibase-connector-php
    
# Usage

A connector requires instantion with an api-key

	$cb = new \Communibase\Connector('<your api key here>');
	
## Example

### Get, max, 5 people named "Tim"

	$cb->search('Person', ['firstName' => 'Tim'], ['limit' => 5])->then(function (results) {
		print_r(results);
	})-wait();

# Methods

All methods are asynchronous and return a [promise](https://github.com/guzzle/promise) of a result.
You can also use the methods in a synchronous fashion by appending "Sync" to the method. i.e. `getByIdSync`.

 * The `entityType` should be the Communibase Entitytype, e.g. "Person", "Invoice", etc. To see all the entity types your API key allows, see the [API docs](https://api.communibase.nl/docs/) and insert your API key there.
 * A `selector` may be provided [MongoDb style](http://docs.mongodb.org/manual/reference/method/db.collection.find/#db.collection.find) as array-definitions.
 * The `params` is a key value store for e.g. `fields`, `limit`, `page` and/or `sort` . See [API docs](https://api.communibase.nl/docs/) for more details. In addition to the nodeJS version of this parameter, the fields value may also be an array of fields. This will work more intuitively in PHP environments.

|Method|Description|Return|
|-|-|
|`search(entityType, selector, params)`|Searches for `entityType` based on the given `selector` and optional `params`|Array of results|
|`getAll(entityType, params)`|Searches for all `entityType` using optional `params`|Array of results|
|`getById(entityType, id, params, version)`|Searches for specific `entityType` via `id` using optional `params` and optional `version`|result|

$cbc->getById($entityType, $id, $params, $version): entity;

$cbc->getByIds($entityType, $ids, $params): entity[];

$cbc->getId($entityType, $selector): string;

$cbc->getIds($entityType, $selector, $params): null|string[];

$cbc->getByRef($ref[, $parent]): entity

$cbc->getTemplate($entityType): array;

$cbc->getHistory($entityType, $id): array;

$cbc->update($entityType, $properties): responseData;

$cbc->destroy($entityType, $id): responseData;

$cbc->generateId(): string - Generate a new, fresh Communibase ID

//Use for Files only to get a string with the binary contents
$cbc->getBinary(id): string;

```

## Entity

An entity is an associative array containing a key/value store of data in Communibase.

E.g.

```
[
	'firstName' => 'Tim',
	'addresses' => [
		[
			'street' => 'Breestraat'
			...
		], ...
	]
]
```

## Error handling

Whenever a function like ```getByIds()``` or ```getByIds()``` returns null, the property ```cbc->lastError``` should be available containing an error message


The connector may throw an error when something goes wrong. Default error handling is as follows:

```
try {
	$person = $cbc->getById('Person', '_DOES_NOT_EXIST_');
} catch (\Communibase\Exception $e) {
	echo $e->getMessage();
}
```

A special type of error handling involves "Validity" errors for posted documents.

```
try {
	$person = $cbc->update('Person', [...]);
} catch (\Communibase\Exception $e) {
	//get an array of errors, per property:
	//	[
	//		[
	//			'field' => '<string>',
	//			'message' => '<string>'
	//		]
	//	]
	print_r($e->getErrors());
}
```

## Query Logging

It is also possible to add a query logger to the connector.

### Stack query data for debug/dev purposes:

    $connector->setQueryLogger(new DebugStack());

Query data available after run via `$connector->getQueryLogger()->queries`

### Echo query for debug/dev purposes (handy for cli):

    $connector->setQueryLogger(new EchoQueryLogger());

Echoes each query to the current output stream.

### Create own query logging:

    $connector->setQueryLogger(new MyOwnQueryLogger());

`MyOwnQueryLogger` implements `QueryLogger` and does something with the data.. possible db/api call

# Contributions / Bugreports

If you're using this and have questions and/or feedback, please file an issue on Github.   
Also we welcome new features and code, so please don't hesitate to get that pull request online!

# Changelog

* 3.0.0 Async first

	All methods are asynchronous (return a [promise](https://github.com/guzzle/promise) of a result), Use `{method}Sync` for synchronous results.

* 2.3.0 Added version support

    Added version parameter to getById.   
    getByIds now returns $ids parameter ordering unless provided otherwise.

* 2.2.1 Bugfix

    Updated the fetchting of binary to work with the HOST header passing.

* 2.2.0 Exceptions: gotta catch 'em all!

    We're building towards better exception handling and as such have moved all outside calls into a single method.   
    This simplifies the logging and HOST-header modifications to a single location. This also prevents code duplication
    for the client calls.

* 2.1.0 Dependency injection

    After the initial moving to Guzzle we decided to change the contstructor of the connector to allow injecting a client.   
    This should help with testing the client etc.

    We've also updated the code to be PSR-2 compliant.

* 2.0.0 Guzzlified

    This release includes the GuzzleHttp client for all communication with the communibase API. This also bumps the minimal   
    PHP version to 5.5. (and thus drops support for earlier versions)

* 1.0.0 Full on Communibase!

    This is the first 1.0 release of the communibase connector. We've been using it internally and as such have added a simple helper method getTemplate for quickly getting an empty entity from Communibase.   
    There is still more work ahead since we plan to move to Guzzle to allow async calls to be made easily aswell as giving it a bit more OO polish (i.e. typecasting the results to their respective PHP equivilants i.e. DateTime objects if it's a Date-property in Communibase)   
    Have fun and feel free to post any issues you may find!
