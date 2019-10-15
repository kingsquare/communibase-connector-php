[![Communibase](https://www.communibase.nl/img/siteLogo.png)](https://www.communibase.nl)

[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/kingsquare/communibase-connector-php/badges/quality-score.png?s=94ea144a5b63afdb4ff9b99991f5ca830ba59d37)](https://scrutinizer-ci.com/g/kingsquare/communibase-connector-php/)
[![Travis CI](https://travis-ci.org/kingsquare/communibase-connector-php.svg)](https://travis-ci.org/kingsquare/communibase-connector-php)
[![Latest Stable Version](https://poser.pugx.org/kingsquare/communibase-connector-php/v/stable.png)](https://packagist.org/packages/kingsquare/communibase-connector-php)
[![License](https://poser.pugx.org/kingsquare/communibase-connector-php/license.png)](https://packagist.org/packages/kingsquare/communibase-connector-php)

A general-purpose Communibase client for PHP projects, compatible with composer packaging-projects.

A connector may be constructed to do REST-calls on the Communibase API.  

The behaviour of this package should always Mimic
the [node-version](https://github.com/kingsquare/communibase-connector-js)

Usage
=====

The easiest way to install the connector is to use [Composer](https://getcomposer.org/):
```
composer require kingsquare/communibase-connector-php
```
Now you should be able to install the package by updating your composer environment ```composer install```   
The connector is available and usable as follows:

```php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
use Communibase\Connector;

$cb = new Connector('<your api key here>');
$peopleNamedTim = $cb->search('Person', ['firstName' => 'Tim'], ['limit' => 5]);
print_r($peopleNamedTim);
```


API
---

"entityType" should be the Communibase Entitytype, e.g. "Person", "Invoice", etc. To see all the entity types your API key allows, see the [API docs](https://api.communibase.nl/docs/) and insert your API key there.

"selectors" may be provided [MongoDb style](http://docs.mongodb.org/manual/reference/method/db.collection.find/#db.collection.find) as array-definitions.

"params" is a key value store for e.g. fields, limit, page and/or sort . See [API docs](https://api.communibase.nl/docs/) for more details. In addition to the nodeJS version of this parameter, the fields value may also be an array of fields. This will work more intuitively in PHP environments.

```php

$cbc->search($entityType, $selector, $params): entity[];

$cbc->getAll($entityType, $params): entity[];

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

Whenever a function like ```getByIds()``` or ```getByIds()``` returns null, the property ```cbc->lastError``` should be available containing an error message


Entity
--
An entity is an associative array containing a key/value store of data in Communibase.

E.g.

```php
[
  'firstName' => 'Tim',
  'addresses' => [
    [
      'street' => 'Breestraat',
      // ...
    ], 
    // ...
  ]
]
```

Error handling
--

The connector may throw an error when something goes wrong. Default error handling is as follows:

```php
try {
  $person = $cbc->getById('Person', '_PERSON_ID_');
} catch (\Communibase\Exception $e) {
  echo $e->getMessage();
}
```

A special type of error handling involves "Validity" errors for posted documents.

```php
try {
  $person = $cbc->update('Person', [...]);
} catch (\Communibase\Exception $e) {
  //get an array of errors, per property:
  //  [
  //    [
  //      'field' => '<string>',
  //      'message' => '<string>'
  //    ]
  //  ]
  print_r($e->getErrors());
}
```

### Query Logging

It is also possible to add a query logger to the connector.

#### Stack query data for debug/dev purposes:

    $connector->setQueryLogger(new DebugStack());

Query data available after run via `$connector->getQueryLogger()->queries`

#### Echo query for debug/dev purposes (handy for cli):

    $connector->setQueryLogger(new EchoQueryLogger());

Echoes each query to the current output stream.

#### Create own query logging:

    $connector->setQueryLogger(new MyOwnQueryLogger());

`MyOwnQueryLogger` implements `QueryLogger` and does something with the data.. possible db/api call

## Contributions / Bugreports

If you're using this app and have questions and/or feedback, please file an issue on Github.   
Also we welcome new features and code, so please don't hesitate to get that pull request online!

