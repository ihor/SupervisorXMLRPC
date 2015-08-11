Supervisor
==========

PHP XML RPC Client for Supervisor (http://supervisord.org)

Installation
------------
Define the following requirement in your composer.json file:
```
"require": {
    "ihor/supervisor": "1.0"
}
```

Usage
-----

```php
$api = new \Supervisor\Api('127.0.0.1', 9001);
$api->getApiVersion();
```
