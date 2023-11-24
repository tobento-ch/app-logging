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
use Closure;

/**
 * LoggersInterface
 */
interface LoggersInterface
{
    /**
     * Add a logger.
     *
     * @param string $name
     * @param Closure|LoggerInterface $logger
     * @return static $this
     */
    public function add(string $name, Closure|LoggerInterface $logger): static;
    
    /**
     * Add an alias for the specified logger.
     *
     * @param string $alias
     * @param string $logger The logger name.
     * @return static $this
     */
    public function addAlias(string $alias, string $logger): static;
    
    /**
     * Returns a logger.
     *
     * @param null|string $name
     * @return LoggerInterface
     */
    public function logger(null|string $name = null): LoggerInterface;
    
    /**
     * Returns a logger by name if exists, otherwise null.
     *
     * @param string $name
     * @return null|LoggerInterface
     */
    public function get(string $name): null|LoggerInterface;
    
    /**
     * Returns true if logger exists, otherwise false.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;
    
    /**
     * Returns all logger names.
     *
     * @return array
     */
    public function names(): array;
    
    /**
     * Returns all created loggers. May be used for resetting loggers.
     *
     * @return array<string, LoggerInterface>
     */
    public function created(): array;
}