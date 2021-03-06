# Setup

Install core

```bash
composer require apitte/core
```

Register DI extension

```yaml
extensions:
    api: Apitte\Core\DI\ApiExtension

api:
    debug: %debugMode%
    catchException: true # Sets if exception should be catched and transformed into response or rethrown to output (debug only)
```

Create entry point

```php
// www/index.php

use Apitte\Core\Application\IApplication;
use App\Bootstrap;

require __DIR__ . '/../vendor/autoload.php';

Bootstrap::boot()
    ->createContainer()
    ->getByType(IApplication::class)
    ->run();
```

## Usage in combination with nette application

```php
// www/index.php

use Apitte\Core\Application\IApplication as ApiApplication;
use App\Bootstrap;
use Nette\Application\Application as UIApplication;

require __DIR__ . '/../vendor/autoload.php';

$isApi = substr($_SERVER['REQUEST_URI'], 0, 4) === '/api';
$container = Bootstrap::boot()->createContainer();

if ($isApi) {
    $container->getByType(ApiApplication::class)->run();
} else {
    $container->getByType(UIApplication::class)->run();
}
```

## Add some plugins

### Decorators

Transform all requests and responses with single class.

See [decorators](decorators.md) chapter for more info.

### Mapping

Validate request parameters, map request body to entity and entity to response body.

See [mapping](mapping.md) chapter for more info.

### Middlewares

PSR-7 middlewares integration

Based on [contributte/middlewares](https://github.com/contributte/middlewares),
integrated by [apitte/middlewares](https://github.com/apitte/middlewares).

See [apitte/middlewares docs](https://github.com/apitte/middlewares) for more info.

### Negotiation

Transforms data into format requested in `Accept` header and in url suffix (`/api/v1/users.xml`)

See [apitte/negotiation docs](https://github.com/apitte/negotiation) for more info.

### Debug

Debug api easily with [negotiation](https://github.com/apitte/negotiation) extension
and display [Tracy debug bar](https://github.com/nette/tracy) along with dumped response data.

See [apitte/debug docs](https://github.com/apitte/debug) for more info.

### Schema

Core plugin (enabled by default) which manage building and validation of whole api schema.

See [schema](schema.md) chapter for more info.

### OpenApi

[OpenApi](https://github.com/OAI/OpenAPI-Specification) integration with [Swagger UI](https://petstore.swagger.io) support.

See [apitte/openapi docs](https://github.com/apitte/openapi) for more info.

### Console

Console commands for your api.

Based on [symfony/console](https://github.com/symfony/console)

See [apitte/console docs](https://github.com/apitte/console) for more info.

### Presenter

Route into your api through a single nette route and presenter.

See [apitte/presenter docs](https://github.com/apitte/presenter) for more info.

## Implementing own plugins

```yaml
api:
    plugins:
        App\Api\Plugin\YourAmazingPlugin:
```

```php
namespace App\Api\Plugin;

use Apitte\Core\DI\Plugin\Plugin;

class YourAmazingPlugin extends Plugin
{

    public static function getName() : string
    {
        return 'pluginName';
    }

    // Add new services, override existing or whatever you want
    // Take a look at existing plugins for inspiration

}
```
