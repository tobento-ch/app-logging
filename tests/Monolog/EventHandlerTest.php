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

namespace Tobento\App\Logging\Test\Monolog;

use PHPUnit\Framework\TestCase;
use Tobento\App\Logging\Monolog\EventHandler;
use Tobento\App\Logging\Event;
use Tobento\Service\Container\Container;
use Tobento\Service\Event\Events;

class EventHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $container = new Container();
        $events = new Events();
        $events->listen(function(Event\MessageLogged $event) use ($container) {
            $container->set('event', $event);
        });
        
        $handler = new EventHandler(
            eventDispatcher: $events,
        );

        $result = $handler->handle([
            'datetime' => new \DateTimeImmutable(),
            'channel' => 'foo',
            'level' => 100,
            'message' => 'msg',
            'context' => ['key' => 'value'],
        ]);
        
        $this->assertFalse($result);        
        $this->assertSame('debug', $container->get('event')->level());
        $this->assertSame('msg', $container->get('event')->message());
        $this->assertSame(['key' => 'value', 'loggerName' => 'foo'], $container->get('event')->context());
    }
    
    public function testHandleWithoutBubble(): void
    {
        $container = new Container();
        $events = new Events();
        $events->listen(function(Event\MessageLogged $event) use ($container) {
            $container->set('event', $event);
        });
        
        $handler = new EventHandler(
            eventDispatcher: $events,
            bubble: false,
        );

        $result = $handler->handle([
            'datetime' => new \DateTimeImmutable(),
            'channel' => 'foo',
            'level' => 100,
            'message' => 'msg',
            'context' => ['key' => 'value'],
        ]);
        
        $this->assertTrue($result);        
        $this->assertSame('debug', $container->get('event')->level());
        $this->assertSame('msg', $container->get('event')->message());
        $this->assertSame(['key' => 'value', 'loggerName' => 'foo'], $container->get('event')->context());
    }
    
    public function testHandleWithoutEventDispatcher(): void
    {
        $handler = new EventHandler(
            eventDispatcher: null,
        );

        $result = $handler->handle([
            'datetime' => new \DateTimeImmutable(),
            'channel' => 'foo',
            'level' => 100,
            'message' => 'msg',
            'context' => ['key' => 'value'],
        ]);
        
        $this->assertFalse($result);
    }
}