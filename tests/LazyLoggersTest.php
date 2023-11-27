<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\App\Logging\Test;

use PHPUnit\Framework\TestCase;
use Tobento\App\Logging\LazyLoggers;
use Tobento\App\Logging\LoggersInterface;
use Tobento\App\Logging\StackLoggerFactory;
use Tobento\App\Logging\LoggingException;
use Tobento\Service\Container\Container;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LazyLoggersTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $loggers = new LazyLoggers(new Container(), []);
        
        $this->assertInstanceof(LoggersInterface::class, $loggers);
    }
    
    public function testUsingFactory()
    {
        $container = new Container();
        
        $loggers = new LazyLoggers(container: $container, loggers: [
            'primary' => [
                'factory' => StackLoggerFactory::class,
                'config' => [
                    'loggers' => ['daily', 'another'],
                ],
            ],
        ]);
        
        $container->set(LoggersInterface::class, $loggers);
        
        $this->assertTrue($loggers->has('primary'));
        $this->assertInstanceof(LoggerInterface::class, $loggers->get('primary'));
        $this->assertSame($loggers->get('primary'), $loggers->get('primary'));
    }
    
    public function testUsingFactoryThrowsLoggingExceptionOnFailure()
    {
        $this->expectException(LoggingException::class);
        
        $loggers = new LazyLoggers(container: new Container(), loggers: [
            'primary' => [
                'factory' => StackLoggerFactory::class,
                'config' => [
                    'loggers' => ['daily', 'another'],
                ],
            ],
        ]);
        
        $loggers->get('primary');
    }
    
    public function testUsingClosure()
    {
        $loggers = new LazyLoggers(container: new Container(), loggers: [
            'primary' => static function (string $name, ContainerInterface $c): LoggerInterface {
                return new NullLogger();
            },
        ]);
        
        $this->assertTrue($loggers->has('primary'));
        $this->assertInstanceof(LoggerInterface::class, $loggers->get('primary'));
        $this->assertSame($loggers->get('primary'), $loggers->get('primary'));
        $this->assertSame($loggers->logger('primary'), $loggers->logger('primary'));
    }
    
    public function testUsingClosureThrowsLoggingExceptionOnFailure()
    {
        $this->expectException(LoggingException::class);
        
        $loggers = new LazyLoggers(container: new Container(), loggers: [
            'primary' => static function (string $name, ContainerInterface $c): LoggerInterface {
                throw new \Exception('error');
            },
        ]);
        
        $loggers->get('primary');
    }
    
    public function testUsingLogger()
    {
        $loggers = new LazyLoggers(container: new Container(), loggers: [
            'primary' => new NullLogger(),
        ]);
        
        $this->assertTrue($loggers->has('primary'));
        $this->assertInstanceof(LoggerInterface::class, $loggers->get('primary'));
        $this->assertSame($loggers->logger('primary'), $loggers->logger('primary'));
    }

    public function testAddMethodUsingLogger()
    {
        $loggers = new LazyLoggers(container: new Container());
        
        $loggers->add(name: 'primary', logger: new NullLogger());
        
        $this->assertTrue($loggers->has('primary'));
        $this->assertInstanceof(LoggerInterface::class, $loggers->get('primary'));
        $this->assertSame($loggers->get('primary'), $loggers->get('primary'));
        $this->assertSame($loggers->logger('primary'), $loggers->logger('primary'));
    }
    
    public function testAddMethodUsingClosure()
    {
        $loggers = new LazyLoggers(container: new Container());
        
        $loggers->add(name: 'primary', logger: static function (): LoggerInterface {
            return new NullLogger();
        });
        
        $this->assertTrue($loggers->has('primary'));
        $this->assertInstanceof(LoggerInterface::class, $loggers->get('primary'));
        $this->assertSame($loggers->get('primary'), $loggers->get('primary'));
        $this->assertSame($loggers->logger('primary'), $loggers->logger('primary'));
    }
    
    public function testAddAliasMethod()
    {
        $loggers = new LazyLoggers(container: new Container());
        $logger = new NullLogger();
        
        $loggers->add(name: 'primary', logger: $logger);
        
        $loggers->addAlias(alias: 'alias', logger: 'primary');
        
        $this->assertTrue($loggers->has('alias'));
        $this->assertSame($logger, $loggers->get('alias'));
        $this->assertSame($logger, $loggers->logger('alias'));
    }
    
    public function testLoggerMethod()
    {
        $loggers = new LazyLoggers(container: new Container());
        $logger = new NullLogger();
        $loggers->add(name: 'primary', logger: $logger);
        
        $this->assertSame($logger, $loggers->logger('primary'));
        $this->assertSame($logger, $loggers->logger());
        
        $loggers = new LazyLoggers(container: new Container());
        $this->assertInstanceof(NullLogger::class, $loggers->logger());
    }
    
    public function testLoggerMethodReturnsDefaultLoggerIfNamedNotExists()
    {
        $loggers = new LazyLoggers(container: new Container());
        $logger = new NullLogger();
        $loggers->add(name: 'primary', logger: $logger);
        
        $this->assertSame($logger, $loggers->logger('foo'));
    }
    
    public function testHasAndGetMethod()
    {
        $loggers = new LazyLoggers(container: new Container(), loggers: [
            'primary' => new NullLogger(),
        ]);
        
        $this->assertTrue($loggers->has('primary'));
        $this->assertFalse($loggers->has('secondary'));
        
        $this->assertInstanceof(LoggerInterface::class, $loggers->get('primary'));
        $this->assertNull($loggers->get('secondary'));
    }
    
    public function testNamesMethod()
    {
        $loggers = new LazyLoggers(container: new Container(), loggers: [
            'primary' => new NullLogger(),
            'secondary' => new NullLogger(),
        ]);
        
        $this->assertSame(['primary', 'secondary'], $loggers->names());
    }
    
    public function testCreatedMethod()
    {
        $loggers = new LazyLoggers(container: new Container(), loggers: [
            'primary' => static function (string $name, ContainerInterface $c): LoggerInterface {
                return new NullLogger();
            },
            'secondary' => new NullLogger(),
        ]);
        
        $this->assertSame(0, count($loggers->created()));
        
        $logger = $loggers->get('primary');
        
        $this->assertSame(1, count($loggers->created()));
    }
}