# Core Class

## <a name="usage"></a>Usage
```php
use ARP\SolrClient2\SolrCore; 

$client = new SolrCore($options);
```
## <a name="addDocument"></a>Add document
```php
$doc = $client->newDocument();
$doc->id = 1;
$doc->text = 'Hello';
$client->addDocument($doc);
$client->commit();
```

## <a name="addDocuments"></a>Add documents
```php
$documents = array();

foreach(array('A', 'B', 'C') as $data) {
    $doc = $client->newDocument();
    $doc->id = $data;
    $doc->text = 'Hello ' . $data;

    $documents[] = $doc;
    unset($doc);
}

$client->addDocuments($documents);
$client->commit();
```

## <a name="appendDocument"></a>Append documents
```php
$client->cacheSize(20480);

foreach(array('A', 'B', 'C') as $data) {
    $doc = $client->newDocument();
    $doc->id = $data;
    $doc->text = 'Hello ' . $data;

    $client->appendDocument($doc);
    unset($doc);
}
$client->commit();
```

## <a name="deleteAll"></a>Delete all
```php
$client->deleteAll();
$client->commit();
```

## <a name="deleteByQuery"></a>Delete by query
```php
$client->deleteByQuery('*:*');
$client->commit();
```