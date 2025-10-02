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
use Tobento\App\Logging\StackLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\TestHandler;

class StackLoggerTest extends TestCase
{
    public function testThatImplementsLoggerInterface()
    {
        $this->assertInstanceof(LoggerInterface::class, new StackLogger());
    }
    
    public function testLogMethod()
    {
        $logger = new Logger('foo');
        $testHandler = new TestHandler();
        $logger->pushHandler($testHandler);
        
        $loggerBar = new Logger('bar');
        $testHandlerBar = new TestHandler();
        $loggerBar->pushHandler($testHandlerBar);
        
        $stackLogger = new StackLogger($logger, $loggerBar);
        $stackLogger->log('error', 'message');
        
        $this->assertTrue($testHandler->hasRecordThatContains('message', Level::Error));
        $this->assertTrue($testHandlerBar->hasRecordThatContains('message', Level::Error));
    }
    
    public function testLogMethods()
    {
        $logger = new Logger('foo');
        $testHandler = new TestHandler();
        $logger->pushHandler($testHandler);
        $stackLogger = new StackLogger($logger);
        
        $stackLogger->emergency('emergency msg');
        $this->assertTrue($testHandler->hasRecordThatContains('emergency msg', Level::Emergency));
        
        $stackLogger->alert('alert msg');
        $this->assertTrue($testHandler->hasRecordThatContains('alert msg', Level::Alert));
        
        $stackLogger->critical('critical msg');
        $this->assertTrue($testHandler->hasRecordThatContains('critical msg', Level::Critical));
        
        $stackLogger->error('error msg');
        $this->assertTrue($testHandler->hasRecordThatContains('error msg', Level::Error));
        
        $stackLogger->warning('warning msg');
        $this->assertTrue($testHandler->hasRecordThatContains('warning msg', Level::Warning));
        
        $stackLogger->notice('notice msg');
        $this->assertTrue($testHandler->hasRecordThatContains('notice msg', Level::Notice));

        $stackLogger->info('info msg');
        $this->assertTrue($testHandler->hasRecordThatContains('info msg', Level::Info));
        
        $stackLogger->debug('debug msg');
        $this->assertTrue($testHandler->hasRecordThatContains('debug msg', Level::Debug));
    }
    
    public function testLogWithoutAnyLogger()
    {
        $stackLogger = new StackLogger();
        $stackLogger->log('error', 'message');
        $this->assertTrue(true);
    }
    
    public function testLoggersMethod()
    {
        $logger = new Logger('foo');
        $testHandler = new TestHandler();
        $logger->pushHandler($testHandler);
        
        $loggerBar = new Logger('bar');
        $testHandlerBar = new TestHandler();
        $loggerBar->pushHandler($testHandlerBar);
        
        $stackLogger = new StackLogger($logger, $loggerBar);
        
        $this->assertSame($logger, $stackLogger->loggers()[0] ?? null);
        $this->assertSame($loggerBar, $stackLogger->loggers()[1] ?? null);
    }
}