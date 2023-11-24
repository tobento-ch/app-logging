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

/**
 * Create StackLogger instances with the loggers specified.
 */
class StackLoggerFactory implements LoggerFactoryInterface
{
    /**
     * Create a new StackLoggerFactory.
     *
     * @param LoggersInterface $loggers
     */
    public function __construct(
        protected LoggersInterface $loggers,
    ) {}
    
    /**
     * Create a new logger based on the configuration.
     *
     * @param string $name
     * @param array $config
     * @return LoggerInterface
     */
    public function createLogger(string $name, array $config): LoggerInterface
    {
        $loggers = [];
        
        foreach($config['loggers'] ?? [] as $name) {
            if (!is_null($logger = $this->loggers->get($name))) {
                $loggers[] = $logger;
            }
        }
        
        return new StackLogger(...$loggers);
    }
}