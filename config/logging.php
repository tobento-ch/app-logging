<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

use Tobento\App\Logging\LoggerFactoryInterface;
use Tobento\App\Logging\StackLoggerFactory;
use Tobento\App\Logging\Monolog\EventHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Monolog\Logger;
use Monolog\Handler;
use Monolog\Formatter;
use Monolog\Processor;
use function Tobento\App\{directory};

return [
    
    /*
    |--------------------------------------------------------------------------
    | Aliases
    |--------------------------------------------------------------------------
    |
    | Configure any aliases you wish to use for your application.
    | See: https://github.com/tobento-ch/app-logging#loggers-interface
    |
    | If using the logger trait you may specify the logger used for the class.
    | See: https://github.com/tobento-ch/app-logging#logger-trait
    |
    */
    
    'aliases' => [
        //'queue' => 'error',
        //SomeClassUsingTheLoggerTrait::class => 'daily',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Loggers
    |--------------------------------------------------------------------------
    |
    | Configure any loggers needed for your application. The first logger
    | is used as the default logger.
    |
    | See: https://github.com/tobento-ch/app-logging#lazy-loggers
    | See: https://github.com/Seldaek/monolog/tree/main/doc
    |
    */
    
    'loggers' => [
        // using closures:
        'daily' => static function (string $name, ContainerInterface $c): LoggerInterface {
            return new Logger(
                name: $name,
                handlers: [
                    new Handler\RotatingFileHandler(
                        filename: directory('app').'storage/logs/daily.log',
                        // The maximal amount of files to keep (0 means unlimited)
                        maxFiles: 30,
                        level: Logger::DEBUG,
                    ),
                    $c->get(EventHandler::class),
                ],
                processors: [
                    new Processor\PsrLogMessageProcessor(),
                ],
            );
        },
        
        'error' => static function (string $name, ContainerInterface $c): LoggerInterface {
            return new Logger(
                name: $name,
                handlers: [
                    new Handler\StreamHandler(
                        stream: directory('app').'storage/logs/error.log',
                        level: Logger::ERROR,
                    ),
                    $c->get(EventHandler::class),
                    // new Handler\ErrorLogHandler(),
                ],
                processors: [
                    new Processor\PsrLogMessageProcessor(),
                ],
            );
        },
        
        // or you may sometimes just create the logger (not lazy):
        'null' => new NullLogger(),
        
        // using a factory:
        /*'name' => [
            // factory must implement LoggerFactoryInterface
            'factory' => StackLoggerFactory::class,
            'config' => [
                'loggers' => ['daily', 'another'],
            ],
        ],*/
    ],
    
];