
## Changelog

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
