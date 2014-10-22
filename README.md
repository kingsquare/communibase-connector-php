[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/kingsquare/communibase-connector-php/badges/quality-score.png?s=94ea144a5b63afdb4ff9b99991f5ca830ba59d37)](https://scrutinizer-ci.com/g/kingsquare/communibase-connector-php/)
[![Latest Stable Version](https://poser.pugx.org/kingsquare/communibase-connector-php/v/stable.png)](https://packagist.org/packages/kingsquare/communibase-connector-php)
[![License](https://poser.pugx.org/kingsquare/communibase-connector-php/license.png)](https://packagist.org/packages/kingsquare/communibase-connector-php)

A general-purpose Communibase client for PHP projects, compatible with composer packaging-projects.

A connector may be constructed to do REST-calls on the Communibase API.  The behaviour of this class should always Mimic
the node.js-version, available at [Github](https://github.com/kingsquare/communibase-connector-js)

Usage
=====

The easiest way to install the connector is to use [Composer](https://getcomposer.org/) and add the following to your project's composer.json file:
```
{
	"require": {
		"kingsquare/communibase-connector-php": "~1"
	}
}
```
Now you should be able to install the package by updating your composer environment ```composer install```
The connector is available and usable as follows:

```
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
use Communibase\Connector;

$cb = new Connector('<your api key here>');
$peopleNamedTim = $cb->search('Person', array('firstName' => 'Tim'), array('limit' => 5));
print_r($peopleNamedTim);
```


API
---

"entityType" should be the Communibase Entitytype, e.g. "Person", "Invoice", etc. To see all the entity types your API key allows, see the [API docs](https://api.communibase.nl/docs/) and insert your API key there.

"selectors" may be provided [MongoDb style](http://docs.mongodb.org/manual/reference/method/db.collection.find/#db.collection.find) as array-definitions.

"params" is a key value store for e.g. fields, limit, page and/or sort . See [API docs](https://api.communibase.nl/docs/) for more details. In addition to the nodeJS version of this parameter, the fields value may also be an array of fields. This will work more intuitively in PHP environments.

```

$cbc->search($entityType, $selector, $params): entity[];

$cbc->getAll($entityType, $params): entity[];

$cbc->getById($entityType, $id, $params): entity;

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

Whenever a function like ```getByIds()``` or ```getByIds()``` returns null, the property ```cbc->lastError``` should be available containing an error message


Entity
--
An entity is an associative array containing a key/value store of data in Communibase.

E.g.

```
array(
	'firstName' => 'Tim',
	'addresses' => array(
		array (
			'street' => 'Breestraat'
			...
		), ...
	)
)
```

Error handling
--

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
	$person = $cbc->update('Person', array(...));
} catch (\Communibase\Exception $e) {
	//get an array of errors, per property:
	//	array(
	//		array(
	//			'field' => '<string>',
	//			'message' => '<string>'
	//		)
	//	)
	print_r($e->getErrors());
}
```
