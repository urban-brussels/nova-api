# Nova API

PHP wrapper for the API of [Nova](https://bric.brussels/en/our-solutions/business-solutions/nova-1?set_language=en).    
Nova is a shared IT platform of the Brussels-Capital Region dedicated to the file management of planning permits, land division permits and environmental licence.        
More features coming soon.

## Installation

```sh
composer require urban-brussels/nova-api
```

## Usage

```php 
use UrbanBrussels\NovaApi\Permit;

// Create instance of Nova Permit
$permit = new Permit('01/PFD/123456');

$permit->getType();

```
