# Nova API

PHP wrapper for the API of [Nova](https://bric.brussels/en/our-solutions/business-solutions/nova-1?set_language=en).
Nova is a shared IT platform of the Brussels-Capital Region dedicated to the file management of planning permits, land
division permits and environmental licences. Permit applications can be viewed online on [OpenPermits.brussels](https://openpermits.brussels/).

## Installation

```sh
composer require urban-brussels/nova-api
```

## Usages

### Class Permit
```php 
use UrbanBrussels\NovaApi\Permit;

// Create instance of Nova Permit, with a regional reference (planning or environment)
$permit = new Permit('01/PFD/123456');

// Get all References in an array (municipal reference, regional reference, uuid, etc)
$permit->getReferences();

// Get Address in an array (street name FR/NL, street number, municipality FR/NL, zipcode)
$permit->getAddress();

// Get Type and Subtype
$permit->getType();
$permit->getSubtype();

// Get an array of Links related to this permit request (Nova, OpenPermits, Nova API)
$permit->getLinks();

// Get Description of the requested permit, in an array FR/NL
$permit->getObject();

// Get Public inquiry dates
$permit->getDateInquiryBegin();
$permit->getDateInquiryEnd();

// Get Submission Date
$permit->getDateSubmission();

// Get Notification Date
$permit->getDateNotification();

// Get a multidimensional array with the Area Typology (existing, projected, authorized areas for each type)
$permit->getAreaTypology();

```

### Class PermitList

```php 
use UrbanBrussels\NovaApi\PermitList;

$list = new PermitList('PU');

// Retrieve all requests in public inquiry for the date 2022-01-01 (PU for planning requests, PE for environmental requests)
$permits = $list->filterByInquiryDate('2022-01-01')->getResults()->all();

// If you use a raw cql_filter, you can query what you want (e.g. every permit request for a given Street + Zipcode)    
$permits = $list->filterByRawCQL("streetnamefr = 'Rue de Dublin' AND zipcode='1050'" )->getResults()->all();

// Filter by Nova References, order by descending submission date
$permits = $list->filterByReferences(['04/PFD/1796029', '04/PFD/1795271'], Attribute::REFERENCE_NOVA)->setOrder(Attribute::DATE_SUBMISSION, Order::DESC)->getResults()->all();

// You now have an array of Permit objects, that can be used in a loop
foreach ($permits as $permit) {
    echo $permit->getRefnova();
    echo $permit->getAddress();
    echo $permit->getDateInquiryEnd();
}
```
