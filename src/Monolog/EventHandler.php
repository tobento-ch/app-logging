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

use Tobento\App\Logging\Event;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * EventHandler
 */
class EventHandler extends AbstractHandler
{
    /**
     * Create a new EventHandler.
     *
     * @param null|EventDispatcherInterface $eventDispatcher
     * @param int|string $level
     * @param bool $bubble
     */
    public function __construct(
        protected null|EventDispatcherInterface $eventDispatcher = null,
        int|string $level = Logger::DEBUG,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);
    }
    
    /**
     * Handle the record.
     *
     * @param array $record
     * @return bool
     */
    public function handle(array $record): bool
    {
        $record['context']['loggerName'] = $record['channel'];
        
        $this->eventDispatcher?->dispatch(new Event\MessageLogged(
            strtolower(Logger::getLevelName($record['level'])),
            $record['message'],
            $record['context'],
        ));
        
        return false === $this->bubble;
    }
}