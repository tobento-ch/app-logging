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
 * LoggerFactoryInterface
 */
interface LoggerFactoryInterface
{
    /**
     * Create a new logger based on the configuration.
     *
     * @param string $name
     * @param array $config
     * @return LoggerInterface
     */
    public function createLogger(string $name, array $config): LoggerInterface;
}