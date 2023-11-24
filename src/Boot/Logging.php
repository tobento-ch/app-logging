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
 
namespace Tobento\App\Logging\Boot;

use Tobento\App\Boot;
use Tobento\App\Boot\Functions;
use Tobento\App\Boot\Config;
use Tobento\App\Migration\Boot\Migration;
use Tobento\App\Logging\LoggersInterface;
use Tobento\App\Logging\LazyLoggers;
use Psr\Log\LoggerInterface;

/**
 * Logging
 */
class Logging extends Boot
{
    public const INFO = [
        'boot' => [
            'installs and loads logging config file',
            'implements logging interfaces',
        ],
    ];

    public const BOOT = [
        Functions::class,
        Config::class,
        Migration::class,
    ];

    /**
     * Boot application services.
     *
     * @param Migration $migration
     * @param Config $config
     * @return void
     */
    public function boot(Migration $migration, Config $config): void
    {
        // install migration:
        $migration->install(\Tobento\App\Logging\Migration\Logging::class);
        
        // interfaces:
        $this->app->set(LoggersInterface::class, function() use ($config): LoggersInterface {
            
            $config = $config->load(file: 'logging.php');
            
            return new LazyLoggers(
                container: $this->app->container(),
                loggers: $config['loggers'] ?? [],
                aliases: $config['aliases'] ?? [],
            );
        });
        
        $this->app->set(LoggerInterface::class, function(): LoggerInterface {            
            return $this->app->get(LoggersInterface::class)->logger();
        });
    }
}