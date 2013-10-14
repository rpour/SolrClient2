# Configuration

## Examples
* [Simple](#simple)
* [Extended](#extended)

## <a name="simple"></a>Simple
This example ...

```php
use ARP\SolrClient2\SolrClient;  
$client = new SolrClient();
```
... is equal to ...

```php
use ARP\SolrClient2\SolrClient; 

$client = new SolrClient(array(
    'host' => 'localhost',
    'port' => 8080,
    'path' => 'solr',
    'version' => 4
));
```
... or

```php
use ARP\SolrClient2\SolrClient; 

$client = new SolrClient();
$client ->host('localhost')
        ->port(8080)
        ->path('solr')
        ->version(4);
```

## <a name="extended"></a>Extended

```php
use ARP\SolrClient2\SolrClient; 

$client = new SolrClient(array(
    'core' => 'core0'
    'params' => array(
        'fq' => 'category:user'
    )
));
```
... <b>is equal to</b> ...
```php
use ARP\SolrClient2\SolrClient; 

$client ->fromCore('core0')
        ->params(array(
            'fq' => 'category:user'
        ));
```
