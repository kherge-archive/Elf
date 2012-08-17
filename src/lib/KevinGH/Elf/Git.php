<?php

/* This file is part of Elf.
 *
 * (c) 2012 Kevin Herrera
 *
 * For the full copyright and license information, please
 * view the LICENSE file that was distributed with this
 * source code.
 */

namespace KevinGH\Elf;

use InvalidArgumentException;
use LogicException;
use KevinGH\Elf\Exception\GitException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Process\Process;

/**
 * Provides support for Git repositories.
 *
 * @author Kevin Herrera <me@kevingh.com>
 */
class Git extends Helper
{
    /**
     * The Git repository path.
     *
     * @var string
     */
    private $path;

    /**
     * Returns the current Git commit hash.
     *
     * @param boolean     $short Get the short hash?
     * @param null|string $path  The repository path.
     *
     * @return string The hash.
     */
    public function getCommit($short = true, $path = null)
    {
        $short = $short ? 'h' : 'H';
        $path = $this->resolvePath($path);

        $process = new Process("git log --pretty=\"%$short\" -n1 HEAD", $path);

        if (0 === $process->run()) {
            return trim($process->getOutput());
        }

        throw GitException::process($process);
    }

    /** {@inheritDoc} */
    public function getName()
    {
        return 'git';
    }

    /**
     * Returns the current Git tag.
     *
     * @param null|string $path The repository path.
     *
     * @return string The tag.
     */
    public function getTag($path = null)
    {
        $path = $this->resolvePath($path);

        $process = new Process('git describe --tags HEAD', $path);

        if (0 === $process->run()) {
            return trim($process->getOutput());
        }

        throw GitException::process($process);
    }

    /**
     * Sets the default repository directory path.
     *
     * @param null|string $path The directory path.
     *
     * @throws InvalidArgumentException If the path is not a directory.
     */
    public function setDefaultPath($path = null)
    {
        if ((null !== $path) && (false === is_dir($path))) {
            throw new InvalidArgumentException(sprintf(
                'The path "%s" is not a directory path or does not exist.',
                $path
            ));
        }

        $this->path = $path;
    }

    /**
     * Resolves the Git repository path argument.
     *
     * @param null|string $path The given path.
     *
     * @return string The resolved path.
     *
     * @throws LogicException If no path is set.
     */
    private function resolvePath($path = null)
    {
        if (null === $path) {
            if (null === $this->path) {
                throw new LogicException('No default Git repository path set.');
            }

            $path = $this->path;
        }

        return $path;
    }
}

