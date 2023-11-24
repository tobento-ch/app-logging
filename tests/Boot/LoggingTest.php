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

namespace Tobento\App\Logging\Test\Boot;

use PHPUnit\Framework\TestCase;
use Tobento\App\Logging\Boot\Logging;
use Tobento\App\Logging\LoggersInterface;
use Tobento\App\Logging\LoggerTrait;
use Tobento\App\Logging\Event;
use Tobento\App\Logging\Monolog\EventHandler;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\App\Boot;
use Tobento\Service\Filesystem\Dir;
use Tobento\Service\Event\EventsInterface;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\Log\LogLevel;

class LoggingTest extends TestCase
{    
    protected function createApp(bool $deleteDir = true): AppInterface
    {
        if ($deleteDir) {
            (new Dir())->delete(__DIR__.'/../app/');
        }
        
        (new Dir())->create(__DIR__.'/../app/');
        
        $app = (new AppFactory())->createApp();
        
        $app->dirs()
            ->dir(realpath(__DIR__.'/../../'), 'root')
            ->dir(realpath(__DIR__.'/../app/'), 'app')
            ->dir($app->dir('app').'config', 'config', group: 'config')
            ->dir($app->dir('root').'vendor', 'vendor');
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testInterfacesAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(Logging::class);
        $app->booting();
        
        $this->assertInstanceof(LoggerInterface::class, $app->get(LoggerInterface::class));
        $this->assertInstanceof(LoggersInterface::class, $app->get(LoggersInterface::class));
    }
    
    public function testDefaulConfigLoggersAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(Logging::class);
        $app->booting();
        $loggers = $app->get(LoggersInterface::class);
        
        $this->assertInstanceof(Logger::class, $app->get(LoggerInterface::class));
        $this->assertInstanceof(Logger::class, $loggers->logger(name: 'daily'));
        $this->assertSame($app->get(LoggerInterface::class), $loggers->logger(name: 'daily'));
        $this->assertInstanceof(Logger::class, $loggers->logger(name: 'error'));
        $this->assertInstanceof(NullLogger::class, $loggers->logger(name: 'null'));
    }
    
    public function testLoggerTraitClassAliasedLoggerIsUsed()
    {
        $foo = new class() {
            use LoggerTrait;

            public function logInfo(string $message): void
            {
                $this->getLogger()->info($message);
            }
        };
        
        $logger = new Logger('foo');
        $testHandler = new TestHandler();
        $logger->pushHandler($testHandler);
        
        $app = $this->createApp();
        $app->boot(Logging::class);
        $app->booting();
        $app->get(LoggersInterface::class)->addAlias($foo::class, 'foo');
        $app->get(LoggersInterface::class)->add('foo', $logger);
        
        $foo->logInfo('message');
        
        $this->assertTrue($testHandler->hasRecordThatContains('message', LogLevel::INFO));
    }

    public function testLogMessage()
    {
        $app = $this->createApp();
        $app->boot(Logging::class);
        $app->booting();
        $app->get(LoggersInterface::class)->logger('null')->info('message');
        $this->assertTrue(true);
    }
    
    public function testLogWithEvent()
    {
        $app = $this->createApp();
        $app->boot(Logging::class);
        $app->boot(\Tobento\App\Event\Boot\Event::class);
        $app->booting();
        
        $events = $app->get(EventsInterface::class);
        $events->listen(function(Event\MessageLogged $event) use ($app) {
            $app->set('event', $event);
        });
        
        // Warning: log file permission denied when deleting the app dir
        // $app->get(LoggerInterface::class)->info('message');
        // so we add one without files
        $app->get(LoggersInterface::class)->add('foo', function () use ($app) {
            return new Logger(
                name: 'foo',
                handlers: [
                    $app->get(EventHandler::class),
                ],
            );
        });  
        
        $app->get(LoggersInterface::class)->logger('foo')->info('message');
        
        $this->assertSame('info', $app->get('event')->level());
        $this->assertSame('message', $app->get('event')->message());
    }
}