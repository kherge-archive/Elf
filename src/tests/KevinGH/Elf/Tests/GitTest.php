<?php

namespace KevinGH\Elf\Tests;

use Herrera\PHPUnit\TestCase;
use KevinGH\Elf\Git;
use RuntimeException;
use Symfony\Component\Process\ProcessBuilder;

class GitTest extends TestCase
{
    private $cwd;
    private $dir;

    /** @var Git */
    private $helper;

    public function testGetCommit()
    {
        $this->assertEquals(
            $this->runCommand('git', array(
                'log',
                '--pretty=%h',
                '-n1',
                'HEAD'
            )),
            $this->helper->getCommit()
        );
    }

    public function testGetCommitNotRepo()
    {
        // cwd isn't working for Process()
        chdir($dir = $this->createDir());

        $this->setExpectedException(
            'KevinGH\\Elf\\Exception\\GitException',
            'Not a git repository'
        );

        $this->helper->getCommit(true, $dir);
    }

    public function testGetName()
    {
        $this->assertEquals('git', $this->helper->getName());
    }

    public function testGetTag()
    {
        $this->assertEquals(
            $this->runCommand('git', array(
                'describe',
                '--tags',
                'HEAD'
            )),
            $this->helper->getTag()
        );
    }

    public function testGetTagNotRepo()
    {
        // cwd isn't working for Process()
        chdir($dir = $this->createDir());

        $this->setExpectedException(
            'KevinGH\\Elf\\Exception\\GitException',
            'Not a git repository'
        );

        $this->helper->getTag($dir);
    }

    public function testSetDefaultPath()
    {
        $dir = $this->createDir();

        $this->helper->setDefaultPath($dir);

        $this->assertEquals($dir, $this->getPropertyValue(
            $this->helper,
            'path')
        );
    }

    public function testSetDefaultPathNotExist()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The path "/does/not/exist" is not a directory path or does not exist.'
        );

        $this->helper->setDefaultPath('/does/not/exist');
    }

    public function testResolvePathNoDefault()
    {
        $this->setExpectedException(
            'LogicException',
            'No default Git repository path set.'
        );

        $this->setPropertyValue($this->helper, 'path', null);

        $this->helper->getTag();
    }

    /**
     * Runs a command line application.
     *
     * @param string $command   The command name or path.
     * @param array  $arguments The command line arguments.
     *
     * @return string The command output.
     *
     * @throws RuntimeException If the command failed.
     */
    protected function runCommand($command, array $arguments = array())
    {
        array_unshift($arguments, $command);

        $process = new ProcessBuilder($arguments);
        $process = $process->getProcess();
        $process->setWorkingDirectory($this->dir);

        if (0 !== $process->run()) {
            throw new RuntimeException(
                $process->getErrorOutput() . $process->getOutput()
            );
        }

        return trim($process->getOutput());
    }

    protected function tearDown()
    {
        chdir($this->cwd);

        parent::tearDown();
    }

    protected function setUp()
    {
        $this->cwd = getcwd();
        $this->dir = $this->createDir();

        chdir($this->dir);
        touch('test.php');

        $this->runCommand('git', array('init'));
        $this->runCommand('git', array('add', 'test.php'));
        $this->runCommand('git', array(
            'commit',
            '-m',
            'Adding the test script.',
            'test.php'
        ));
        $this->runCommand('git', array(
            'tag',
            '1.0.0'
        ));

        $this->helper = new Git();

        $this->setPropertyValue($this->helper, 'path', $this->dir);
    }
}
