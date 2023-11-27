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
use Tobento\App\Logging\LoggerTrait;
use Tobento\App\Logging\LoggersInterface;
use Tobento\App\Logging\LazyLoggers;
use Tobento\Service\HelperFunction\Functions;
use Tobento\Service\Container\Container;
use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;

class LoggerTraitTest extends TestCase
{
    use LoggerTrait;
    
    public function testUsesAddedLogger()
    {
        $logger = new NullLogger();
        $this->setLogger($logger);
        $this->assertSame($logger, $this->getLogger());
    }
    
    public function testUsesNullLoggerIfNoneExists()
    {
        $logger = $this->getLogger();
        $this->assertInstanceOf(NullLogger::class, $this->getLogger());
        $this->assertSame($logger, $this->getLogger());
    }
    
    public function testUsesClassNamedLoggerIfExists()
    {
        $container = new Container();
        $logger = new NullLogger();
        $loggers = new LazyLoggers(container: $container, loggers: [
            static::class => $logger,
        ]);
        $container->set(LoggersInterface::class, $loggers);
        
        $functions = new Functions();
        $functions->set(ContainerInterface::class, $container);
        
        $this->assertSame($logger, $this->getLogger());
        $this->assertSame($logger, $this->getLogger());
    }
    
    public function testUsingNamedLoggerReturnsNullLoggerIfNoneExists()
    {
        $this->assertInstanceOf(NullLogger::class, $this->getLogger(name: 'foo'));
    }
    
    public function testUsingNamedLoggerReturnsDefaultLoggerIfNotExists()
    {
        $container = new Container();
        $logger = new NullLogger();
        $loggers = new LazyLoggers(container: $container, loggers: [
            'first' => $logger,
        ]);
        $container->set(LoggersInterface::class, $loggers);
        
        $functions = new Functions();
        $functions->set(ContainerInterface::class, $container);
        
        $this->assertSame($logger, $this->getLogger(name: 'foo'));
    }
    
    public function testUsesNamedLogger()
    {
        $container = new Container();
        $logger = new NullLogger();
        $loggers = new LazyLoggers(container: $container, loggers: [
            'foo' => $logger,
        ]);
        $container->set(LoggersInterface::class, $loggers);
        
        $functions = new Functions();
        $functions->set(ContainerInterface::class, $container);
        
        $this->assertSame($logger, $this->getLogger('foo'));
        $this->assertSame($logger, $this->getLogger('foo'));
    }
    
    public function testUsesNullLoggerIfContainerHasNoLoggers()
    {
        $container = new Container(); 
        $functions = new Functions();
        $functions->set(ContainerInterface::class, $container);
        
        $logger = $this->getLogger();
        $this->assertInstanceOf(NullLogger::class, $this->getLogger());
        $this->assertSame($logger, $this->getLogger());
    }
}