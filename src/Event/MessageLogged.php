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

namespace Tobento\App\Logging\Event;

/**
 * MessageLogged
 */
final class MessageLogged
{
    /**
     * Create a new MessageLogged.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function __construct(
        private string $level,
        private string $message,
        private array $context = [],
    ) {}
    
    /**
     * Returns the level.
     *
     * @return string
     */
    public function level(): string
    {
        return $this->level;
    }
    
    /**
     * Returns the message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }
    
    /**
     * Returns the context.
     *
     * @return array
     */
    public function context(): array
    {
        return $this->context;
    }
}