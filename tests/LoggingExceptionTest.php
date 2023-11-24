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

namespace Tobento\App\Logging\Test;

use PHPUnit\Framework\TestCase;
use Tobento\App\Logging\LoggingException;
use Exception;

class LoggingExceptionTest extends TestCase
{
    public function testException()
    {
        $this->assertInstanceof(Exception::class, new LoggingException());
    }
}