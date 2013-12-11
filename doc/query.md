# Query Class
* [Usage](doc/query.md#usage)
* [Select](doc/query.md#select)
* [Offset and limit](doc/query.md#offset)
* [Where](doc/query.md#where)

## <a href="../README.md">&laquo;</a> <a name="usage"></a>Usage
```php
use ARP\SolrClient2\SolrQuery; 

$client = new SolrQuery($options);
```

## <a href="../README.md">&laquo;</a> <a name="select"></a>Usage
```php
$client
     ->select('*,score')
     ->exec('*:*');
```

## <a href="../README.md">&laquo;</a> <a name="offset"></a>Usage
```php
$client
     ->limit(10)
     ->offset(10)
     ->exec('*:*');

// or

$client
     ->limit(10)
     ->page(2)
     ->exec('*:*');;
```

## <a href="../README.md">&laquo;</a> <a name="where"></a>Usage
```php
$client
     ->where('user', 'guest')
     ->exec('*:*');
```
