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

namespace Tobento\App\Logging;

use Psr\Log\LoggerInterface;
use Psr\Log\AbstractLogger;

/**
 * StackLogger
 */
class StackLogger extends AbstractLogger
{
    /**
     * @var array<array-key, LoggerInterface>
     */
    protected array $loggers = [];
    
    /**
     * Create a new StackLogger.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface ...$logger,
    ) {
        $this->loggers = $logger;
    }

    /**
     * Returns the loggers.
     *
     * @return array<array-key, LoggerInterface>
     */
    public function loggers(): array
    {
        return $this->loggers;
    }
    
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string|\Stringable $message
     * @param array  $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        foreach($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }
}