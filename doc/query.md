# Query Class
* [Usage](query.md#usage)
* [Select](query.md#select)
* [Offset and limit](query.md#offset)
* [Where](query.md#where)

## <a href="../README.md">&laquo;</a> <a name="usage"></a>Usage
```php
use ARP\SolrClient2\SolrQuery; 

$client = new SolrQuery($options);
```

## <a href="../README.md">&laquo;</a> <a name="select"></a>Select
```php
$client
     ->select('*,score')
     ->exec('*:*');
```

## <a href="../README.md">&laquo;</a> <a name="offset"></a>Offset and limit
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

## <a href="../README.md">&laquo;</a> <a name="where"></a>Where
```php
$client
     ->where('user', 'guest')
     ->exec('*:*');
```
