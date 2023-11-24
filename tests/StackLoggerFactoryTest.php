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
use Tobento\App\Logging\StackLoggerFactory;
use Tobento\App\Logging\LoggerFactoryInterface;
use Tobento\App\Logging\StackLogger;
use Tobento\App\Logging\LazyLoggers;
use Tobento\Service\Container\Container;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class StackLoggerFactoryTest extends TestCase
{
    public function testThatImplementsLoggerFactoryInterface()
    {
        $factory = new StackLoggerFactory(
            loggers: new LazyLoggers(new Container())
        );
        
        $this->assertInstanceof(LoggerFactoryInterface::class, $factory);
    }

    public function testCreateLoggerMethod()
    {
        $factory = new StackLoggerFactory(
            loggers: new LazyLoggers(
                container: new Container(),
                loggers: ['null' => new NullLogger()],
            )
        );
        
        $logger = $factory->createLogger(name: 'name', config: [
            'loggers' => ['null', 'foo'],
        ]);
        
        $this->assertInstanceof(LoggerInterface::class, $logger);
        $this->assertInstanceof(StackLogger::class, $logger);
        $this->assertSame(1, count($logger->loggers()));
    }
    
    public function testCreateLoggerMethodWithoutAnyLogger()
    {
        $factory = new StackLoggerFactory(
            loggers: new LazyLoggers(new Container())
        );
        
        $logger = $factory->createLogger(name: 'name', config: [
            'loggers' => ['daily'],
        ]);
        
        $this->assertInstanceof(LoggerInterface::class, $logger);
        $this->assertInstanceof(StackLogger::class, $logger);
        $this->assertSame(0, count($logger->loggers()));
    }
}