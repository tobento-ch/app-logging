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

namespace Tobento\App\Logging\Test\Event;

use PHPUnit\Framework\TestCase;
use Tobento\App\Logging\Event\MessageLogged;

class MessageLoggedTest extends TestCase
{
    public function testEvent()
    {
        $event = new MessageLogged(
            level: 'debug',
            message: 'message',
            context: ['key' => 'value'],
        );
        
        $this->assertSame('debug', $event->level());
        $this->assertSame('message', $event->message());
        $this->assertSame(['key' => 'value'], $event->context());
    }
}