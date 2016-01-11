Supervisor
==========

PHP XML RPC Client for Supervisor (http://supervisord.org)

Installation
------------
Define the following requirement in your composer.json file:
```
"require": {
    "ihor/supervisor": "0.1.1"
}
```

Usage
-----

```php
// Create Supervisor API instance
$api = new \Supervisor\Api('127.0.0.1', 9001 /* username, password */);

// Call Supervisor API
$api->getApiVersion();

// That's all!
```
