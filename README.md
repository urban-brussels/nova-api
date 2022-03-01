# Nova API

PHP wrapper for the API of [Nova](https://bric.brussels/en/our-solutions/business-solutions/nova-1?set_language=en).
Nova is a shared IT platform of the Brussels-Capital Region dedicated to the file management of planning permits, land
division permits and environmental licences. Permit applications can be viewed online on [OpenPermits.brussels](https://openpermits.brussels/).

## Installation

```sh
composer require urban-brussels/nova-api
```

## Usage

```php 
use UrbanBrussels\NovaApi\Attribute;
use UrbanBrussels\NovaApi\PermitQuery;
use UrbanBrussels\NovaApi\PermitCollection;

$query = new PermitQuery('PU'); // Create a query for planning (PU) or environmental (PE) licences
$permits = $query
    ->filterByReferences(['04/PFD/1796029', '04/PFD/1795271'], Attribute::REFERENCE_NOVA) // Filter by Nova References
    ->setOrder(Attribute::DATE_SUBMISSION, 'DESC') // Order by descending submission date
    ->setLimit(2); // Limit to 2 results

// You now have a PermitCollection object, that can be used in a loop
foreach ($permits->getPermits() as $permit) 
{
    echo $permit->getRefnova();
    echo $permit->getAddress();
    echo $permit->getDateInquiryEnd();
    echo $permit->hasActiveInquiry();
}

// Other available getters

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

### Other queries examples

```php 
use UrbanBrussels\NovaApi\PermitQuery;
use UrbanBrussels\NovaApi\PermitCollection;

$query = new PermitQuery('PU');

// Retrieve all requests in public inquiry for the date 2022-01-01 (PU for planning requests, PE for environmental requests)
$permits = $query->filterByInquiryDate('2022-01-01');

// If you use a raw cql_filter, you can query what you want (e.g. every permit request for a given Street + Zipcode)    
$permits = $query->filterByRawCQL("streetnamefr = 'Rue de Dublin' AND zipcode='1050'" )->getResults()->all();
```
### Advantages    
This library fixes the following inconsistencies in the Nova WFS webservices
- Different attributes names for PE and PU
- Attributes names mixing french and english
- Different DateTime patterns (with or without microseconds)
- Boolean values returned as string (e.g.: incidences, mpp)
- Too deep json for suspensions
- Integer values returned as string (e.g. novaseq, zipcode)
- Status must be guessed mixing different attributes
- Redundancy
- Incorrect values (e.g. submissions in the year 1111)
- Number "to" same as number "from" in addresses