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

namespace Tobento\App\Logging\Monolog;

use Psr\EventDispatcher\EventDispatcherInterface;
use Monolog\Handler\AbstractHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Tobento\App\Logging\Event;

class EventHandler extends AbstractHandler
{
    /**
     * Create a new EventHandler.
     *
     * @param null|EventDispatcherInterface $eventDispatcher
     * @param int|string|Level $level
     * @param bool $bubble
     */
    public function __construct(
        protected null|EventDispatcherInterface $eventDispatcher = null,
        int|string|Level $level = Level::Debug,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);
    }
    
    /**
     * Handle the record.
     *
     * @param LogRecord $record
     * @return bool
     */
    public function handle(LogRecord $record): bool
    {
        $context = $record->context;
        $context['loggerName'] = $record->channel;
        
        $this->eventDispatcher?->dispatch(new Event\MessageLogged(
            Logger::toMonologLevel($record->level)->toPsrLogLevel(),
            $record->message,
            $context,
        ));
        
        return false === $this->bubble;
    }
}