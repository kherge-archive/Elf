<?php

/* This file is part of Elf.
 *
 * (c) 2012 Kevin Herrera
 *
 * For the full copyright and license information, please
 * view the LICENSE file that was distributed with this
 * source code.
 */

namespace KevinGH\Elf\Exception;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * A Git exception.
 *
 * @author Kevin Herrera <me@kevingh.com>
 */
class GitException extends RuntimeException
{
    /**
     * Creates a new exception using the Process's output.
     *
     * @param Process $process The process.
     */
    public static function process(Process $process)
    {
        return new self($process->getOutput() . $process->getErrorOutput());
    }
}

