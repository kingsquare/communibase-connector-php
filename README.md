A general-purpose Communibase client for PHP projects, compatible with composer packaging-projects.

A connector may be constructed to do REST-calls on the Communibase API.  The behaviour of this class should always Mimic
the node.js-version, available at [Github](https://github.com/kingsquare/communibase-connector-php)

Usage
=====

Install composer, Initialize it (```php composer.phar init```) and add the connector as follows:

```
{
	"require": {
		"kingsquare/communibase-connector-php": "*"
	},
	"repositories": [
		{
			"type": "vcs",
			"url":  "git@github.com:kingsquare/communibase-connector-php"
		}
	]
}

```
Now install the package using ``` php composer.phar install ```

The connector is available and usable as follows:

```
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
use Communibase\Connector;

$cb = new Connector('<your api key here>');
$tims = $db->search('Person', array('firstName' => 'Tim'), array('limit' => 5));
print_r($tims);
```


API
---

"objectType" should be the Communibase Entitytype, e.g. "Person", "Invoice", etc.

"selectors" may be provided [MongoDb style](http://docs.mongodb.org/manual/reference/method/db.collection.find/#db.collection.find).

"params" is a key value store for e.g. fields, limit, page and/or sort . See [API docs](https://api.communibase.nl/docs/) for more details.

```

$cbc->getById($objectType, $objectId, $params): document;

$cbc->getByIds($objectType, $objectIds, $params): document[];

$cbc->getAll($objectType, $params): document[];

$cbc->getId($objectType, $selector): string;

$cbc->getByRef($ref[, $parentDocument]): document

$cbc->getIds($objectType, $selector, $params): null|string[];

$cbc->search($objectType, $selector, $params): null|document[];

$cbc->update($objectType, $document): responseData;

$cbc->destroy($objectType, $objectId): responseData;

$cbc->generateId(): string - Generate a new, fresh MongoDB object ID

//Use for Files only to get a string with the binary contents
$cbc->getBinary(objectId): string;

```

Whenever a function like ```getByIds()``` or ```getByIds()``` returns null, the property cbc->lastError should be available containing an error message


DOCUMENT

A document is an associative array containing a key/value store of an entity in Communibase.

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

RESPONSEDATA

Response data is an associative array in the following format:

```
array(
	success => true|false
	errors => array(
		array(
			'field' => '<string>',
			'message' => '<string>'
		)
	)
)
```