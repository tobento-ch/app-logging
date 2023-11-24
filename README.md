# App Logging

Logging support for the app using the [Monolog](https://github.com/Seldaek/monolog) library.

## Table of Contents

- [Getting Started](#getting-started)
    - [Requirements](#requirements)
- [Documentation](#documentation)
    - [App](#app)
    - [Logging Boot](#logging-boot)
        - [Logging Config](#logging-config)
    - [Logger Trait](#logger-trait)
    - [Loggers](#loggers)
        - [Lazy Loggers](#lazy-loggers)
    - [Logger Factories](#Logger-factories)
        - [Stack Logger Factory](#stack-logger-factory)
    - [Events](#events)
    - [Interfaces](#interfaces)
        - [Loggers Interface](#loggers-interface)
        - [Logger Factory Interface](#logger-factory-interface)
- [Credits](#credits)
___

# Getting Started

Add the latest version of the app logging project running this command.

```
composer require tobento/app-logging
```

## Requirements

- PHP 8.0 or greater

# Documentation

## App

Check out the [**App Skeleton**](https://github.com/tobento-ch/app-skeleton) if you are using the skeleton.

You may also check out the [**App**](https://github.com/tobento-ch/app) to learn more about the app in general.

## Logging Boot

The logging boot does the following:

* installs and loads logging config file
* implements logging interfaces

```php
use Tobento\App\AppFactory;
use Tobento\App\Logging\LoggersInterface;
use Psr\Log\LoggerInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots
$app->boot(\Tobento\App\Logging\Boot\Logging::class);
$app->booting();

// Implemented interfaces:
$logger = $app->get(LoggerInterface::class);
$loggers = $app->get(LoggersInterface::class);

// Run the app
$app->run();
```

### Logging Config

The configuration for the logging is located in the ```app/config/logging.php``` file at the default App Skeleton config location where you can specify the loggers for your application.

The [Lazy Loggers](#lazy-loggers) is used for the loggers.

## Logger Trait

You may use the logger trait to quickly access a logger instance and log messages:

```php
use Tobento\App\Logging\LoggerTrait;

class SomeService
{
    use LoggerTrait;

    public function someAction(): void
    {
        $this->getLogger()->info('Some info');
        // is same as:
        $this->getLogger(name: static::class)->info('Some info');
        // if the named logger does not exists,
        // the default logger will be used.
        
        // or using another named logger:
        $this->getLogger(name: 'daily')->info('Some info');
    }
}
```

Next, you may define the logger used for your service.

In the [Logging Config](#logging-config) file, define your service class and the logger you want to use:

```php
/*
|--------------------------------------------------------------------------
| Aliases
|--------------------------------------------------------------------------
*/

'aliases' => [
    SomeService::class => 'daily',
],
```

Alternatively, you may request the ```LoggersInterface::class``` from your app or inject it in any class and use the ```addAlias``` method:

```php
use Tobento\App\Logging\LoggersInterface;

$app->get(LoggersInterface::class)->addAlias(alias: SomeService::class, logger: 'daily');
```

## Loggers

### Lazy Loggers

The ```LazyLoggers::class``` creates the loggers only on demand.

```php
use Tobento\App\Logging\LazyLoggers;
use Tobento\App\Logging\LoggerFactoryInterface;
use Tobento\App\Logging\LoggersInterface;
use Tobento\App\Logging\StackLoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

$loggers = new LazyLoggers(
    container: $container, // ContainerInterface
    loggers: [
        // using a closure:
        'daily' => static function (string $name, ContainerInterface $c): LoggerInterface {
            // create logger ...
            return $logger;
        },
        
        // using a factory:
        'stacked' => [
            // factory must implement LoggerFactoryInterface
            'factory' => StackLoggerFactory::class,
            'config' => [
                'loggers' => ['daily', 'another'],
            ],
        ],
        
        // or you may sometimes just create a logger (not lazy):
        'null' => new NullLogger(),
    ],
);

var_dump($loggers instanceof LoggersInterface);
// bool(true)
```

Check out the [Logger Factories](#logger-factories) section for the available logger factories.

Check out the [Loggers Interface](#loggers-interface) section to learn more about it.

## Logger Factories

### Stack Logger Factory

The ```StackLoggerFactory::class``` creates a stack logger with the specified loggers.

```php
use Tobento\App\Logging\StackLoggerFactory;
use Tobento\App\Logging\LoggerFactoryInterface;
use Tobento\App\Logging\LoggersInterface;

$factory = new StackLoggerFactory(
    loggers: $loggers // LoggersInterface
);

var_dump($factory instanceof LoggerFactoryInterface);
// bool(true)
```

Check out the [Loggers](#loggers) section for the available loggers.

**Create logger**

```php
use Psr\Log\LoggerInterface;
use Tobento\App\Logging\StackLogger;

$logger = $factory->createLogger(name: 'stacked', config: [
    // specify the loggers you want to be stacked:
    'loggers' => ['daily', 'syslog'],
]);

var_dump($logger instanceof LoggerInterface);
// bool(true)

var_dump($logger instanceof StackLogger);
// bool(true)
```

## Events

**Available Events**

```php
use Tobento\App\Logging\Event;
```

| Event | Description |
| --- | --- |
| ```Event\MessageLogged::class``` | The event will dispatch **when** a message is logged |

**Events Support**

You may add the ```EventHandler::class``` in the [Logging Config](#logging-config) file and install the [App Event](https://github.com/tobento-ch/app-event) bundle to support events.

```php
use Tobento\App\Logging\Monolog\EventHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

'loggers' => [

    'daily' => static function (string $name, ContainerInterface $c): LoggerInterface {
        return new Logger(
            name: $name,
            handlers: [
                // support events:
                $c->get(EventHandler::class),
            ],
        );
    },
],
```

## Interfaces

### Loggers Interface

Check out the available [Loggers](#loggers) implementing the ```Tobento\App\Logging\LoggersInterface::class```.

**add**

With the ```add``` method you can add a new logger:

```php
use Psr\Log\LoggerInterface;

$loggers->add(name: 'daily', logger: $logger); // LoggerInterface

// or using a closure:
$loggers->add(name: 'daily', logger: static function (): LoggerInterface {
    return $createdLogger;
});
```

**addAlias**

With the ```addAlias``` method you can add an alias for the specified logger:

```php
$loggers->addAlias(alias: 'alias', logger: 'daily');

// get a logger by an alias:
$logger = $loggers->get('alias');
// returns the 'daily' logger if exists.
```

**logger**

The ```logger``` method returns a logger:

```php
use Psr\Log\LoggerInterface;

// get the default logger:
$logger = $loggers->logger();

// get a named logger:
$logger = $loggers->logger(name: 'daily');

// get an aliased logger:
$logger = $loggers->logger(name: 'alias');

var_dump($logger instanceof LoggerInterface);
// bool(true)
```

**get**

The ```get``` method returns a logger if exists, otherwise ```null```:

```php
use Psr\Log\LoggerInterface;

// get a named logger:
$logger = $loggers->get(name: 'daily');

// get an aliased logger:
$logger = $loggers->get(name: 'alias');

var_dump($logger instanceof LoggerInterface);
// bool(true) or NULL if not exist
```

**has**

The ```has``` method returns ```true``` if the logger exists, otherwise ```false```:

```php
$has = $loggers->has(name: 'daily');

// with an alias:
$has = $loggers->has(name: 'alias');
```

**names**

The ```names``` method returns all logger names:

```php
$names = $loggers->names();

var_dump($names);
// array(1) {[0]=> string(5) "daily"}
```

**created**

The ```created``` method returns all created loggers which may be used to reset or clear loggers:

```php
use Psr\Log\LoggerInterface;

$loggers = $loggers->created();
// array<string, LoggerInterface>
```

### Logger Factory Interface

Check out the available [Logger Factories](#logger-factories) implementing the ```Tobento\App\Logging\LoggerFactoryInterface::class```.

**createLogger**

The ```createLogger``` method creates a new logger instance based on the configuration:

```php
use Psr\Log\LoggerInterface;

$logger = $loggerFactory->createLogger(
    name: 'stacked', // a logger name
    config: [], // any data for creating the logger
);

var_dump($logger instanceof LoggerInterface);
// bool(true)
```

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)
- [Seldaek Monolog](https://github.com/Seldaek/monolog)