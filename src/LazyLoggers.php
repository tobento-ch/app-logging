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

use Tobento\Service\Autowire\Autowire;
use Tobento\Service\Autowire\AutowireException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Closure;
use Throwable;

/**
 * LazyLoggers
 */
class LazyLoggers implements LoggersInterface
{
    /**
     * @var Autowire
     */
    protected Autowire $autowire;
    
    /**
     * @var array<string, LoggerInterface>
     */
    protected array $createdLoggers = [];
    
    /**
     * Create a new LazyLoggers.
     *
     * @param ContainerInterface $container
     * @param array $loggers
     * @param array<string, string> $aliases
     */
    public function __construct(
        ContainerInterface $container,
        protected array $loggers = [],
        protected array $aliases = [],
    ) {
        $this->autowire = new Autowire($container);
    }
    
    /**
     * Add a logger.
     *
     * @param string $name
     * @param Closure|LoggerInterface $logger
     * @return static $this
     */
    public function add(string $name, Closure|LoggerInterface $logger): static
    {
        $this->loggers[$name] = $logger;
        
        return $this;
    }
    
    /**
     * Add an alias for the specified logger.
     *
     * @param string $alias
     * @param string $logger The logger name.
     * @return static $this
     */
    public function addAlias(string $alias, string $logger): static
    {
        $this->aliases[$alias] = $logger;
        
        return $this;
    }
    
    /**
     * Returns a logger.
     *
     * @param null|string $name
     * @return LoggerInterface
     */
    public function logger(null|string $name = null): LoggerInterface
    {
        if (is_null($name)) {
            return $this->getDefaultLogger();
        }
        
        if (!is_null($logger = $this->get($name))) {
            return $logger;
        }
        
        return $this->getDefaultLogger();
    }
    
    /**
     * Returns a logger by name if exists, otherwise null.
     *
     * @param string $name
     * @return null|LoggerInterface
     */
    public function get(string $name): null|LoggerInterface
    {
        $name = $this->aliases[$name] ?? $name;
        
        if (isset($this->createdLoggers[$name])) {
            return $this->createdLoggers[$name];
        }
        
        if (!array_key_exists($name, $this->loggers)) {
            return null;
        }

        if ($this->loggers[$name] instanceof LoggerInterface) {
            return $this->loggers[$name];
        }
        
        // create logger from callable:
        if (is_callable($this->loggers[$name])) {
            try {
                return $this->createdLoggers[$name] = $this->autowire->call($this->loggers[$name], ['name' => $name]);
            } catch (Throwable $e) {
                throw new LoggingException($e->getMessage(), (int)$e->getCode(), $e);
            }
        }
        
        // create logger from factory:
        if (!isset($this->loggers[$name]['factory'])) {
            return null;
        }
        
        try {
            $factory = $this->autowire->resolve($this->loggers[$name]['factory']);
        } catch (AutowireException $e) {
            throw new LoggingException($e->getMessage(), (int)$e->getCode(), $e);
        }
        
        if (! $factory instanceof LoggerFactoryInterface) {
            return null;
        }
        
        $config = $this->loggers[$name]['config'] ?? [];
        
        return $this->createdLoggers[$name] = $factory->createLogger($name, $config);
    }
    
    /**
     * Returns true if logger exists, otherwise false.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        $name = $this->aliases[$name] ?? $name;
        
        return array_key_exists($name, $this->loggers);
    }
    
    /**
     * Returns all logger names.
     *
     * @return array
     */
    public function names(): array
    {
        return array_keys($this->loggers);
    }
    
    /**
     * Returns all created loggers. May be used for resetting loggers.
     *
     * @return array<string, LoggerInterface>
     */
    public function created(): array
    {
        return $this->createdLoggers;
    }
    
    /**
     * Returns the default logger.
     *
     * @return LoggerInterface
     */
    protected function getDefaultLogger(): LoggerInterface
    {
        $name = (string) array_key_first($this->loggers);
        
        if (!is_null($logger = $this->get($name))) {
            return $logger;
        }
        
        return new NullLogger();
    }
}