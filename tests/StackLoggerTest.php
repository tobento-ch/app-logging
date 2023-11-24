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
        
        $this->assertTrue($testHandler->hasRecordThatContains('message', LogLevel::ERROR));
        $this->assertTrue($testHandlerBar->hasRecordThatContains('message', LogLevel::ERROR));
    }
    
    public function testLogMethods()
    {
        $logger = new Logger('foo');
        $testHandler = new TestHandler();
        $logger->pushHandler($testHandler);
        $stackLogger = new StackLogger($logger);
        
        $stackLogger->emergency('emergency msg');
        $this->assertTrue($testHandler->hasRecordThatContains('emergency msg', LogLevel::EMERGENCY));
        
        $stackLogger->alert('alert msg');
        $this->assertTrue($testHandler->hasRecordThatContains('alert msg', LogLevel::ALERT));
        
        $stackLogger->critical('critical msg');
        $this->assertTrue($testHandler->hasRecordThatContains('critical msg', LogLevel::CRITICAL));
        
        $stackLogger->error('error msg');
        $this->assertTrue($testHandler->hasRecordThatContains('error msg', LogLevel::ERROR));
        
        $stackLogger->warning('warning msg');
        $this->assertTrue($testHandler->hasRecordThatContains('warning msg', LogLevel::WARNING));
        
        $stackLogger->notice('notice msg');
        $this->assertTrue($testHandler->hasRecordThatContains('notice msg', LogLevel::NOTICE));

        $stackLogger->info('info msg');
        $this->assertTrue($testHandler->hasRecordThatContains('info msg', LogLevel::INFO));
        
        $stackLogger->debug('debug msg');
        $this->assertTrue($testHandler->hasRecordThatContains('debug msg', LogLevel::DEBUG));
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