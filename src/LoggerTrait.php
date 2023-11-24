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
use Psr\Log\NullLogger;
use Psr\Container\ContainerInterface;
use Tobento\Service\HelperFunction\Functions;

/**
 * LoggerTrait
 */
trait LoggerTrait
{
    /**
     * @var null|LoggerInterface
     */
    private null|LoggerInterface $logger = null;

    /**
     * Set a logger.
     *
     * @param LoggerInterface $logger
     * @return static $this
     */
    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        
        return $this;
    }

    /**
     * Returns a logger.
     *
     * @param null|string $name
     * @return LoggerInterface
     */
    protected function getLogger(null|string $name = null): LoggerInterface
    {
        if (!is_null($name)) {
            // specific logger:
            return $this->fetchLogger($name);
        }

        if (!is_null($this->logger)) {
            return $this->logger;
        }

        // We use the class name as logger name by default:
        return $this->logger = $this->fetchLogger(static::class);
    }
    
    /**
     * Fetch a logger by name.
     *
     * @param string $name
     * @return LoggerInterface
     */
    private function fetchLogger(string $name): LoggerInterface
    {
        if (! Functions::has(ContainerInterface::class)) {
            return $this->logger ?? new NullLogger();
        }
        
        $container = Functions::get(ContainerInterface::class);
        
        if ($container->has(LoggersInterface::class)) {
            return $container->get(LoggersInterface::class)->logger($name);
        }
        
        return $this->logger ?? new NullLogger();
    }
}