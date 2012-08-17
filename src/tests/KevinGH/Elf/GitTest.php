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

class GitTest extends HelperTester
{
    protected $helperClass = 'KevinGH\\Elf\\Git';

    protected function setUp()
    {
        parent::setUp();

        $this->helper->setDefaultPath($this->currentDir);
    }

    public function testGetCommit()
    {
        if ($this->checkGit($this)) {
            return;
        }

        $this->command('git init');
        $this->command('touch test');
        $this->command('git add test');
        $this->command('git commit -m "Adding test."');

        $short = $this->command('git log --pretty="%h" -n1 HEAD');
        $long = $this->command('git log --pretty="%H" -n1 HEAD');

        $this->assertEquals($short, $this->helper->getCommit(true));
        $this->assertEquals($long, $this->helper->getCommit(false));
    }

    /**
     * @2expectedException KevinGH\Elf\Exception\GitException
     */
    public function testGetCommitNotRepo()
    {
        $this->helper->getCommit(true, $this->currentDir);
    }

    public function testGetName()
    {
        if ($this->checkGit($this)) {
            return;
        }

        $this->assertEquals('git', $this->helper->getName());
    }

    public function testGetTag()
    {
        if ($this->checkGit($this)) {
            return;
        }

        $this->command('git init');
        $this->command('touch test');
        $this->command('git add test');
        $this->command('git commit -m "Adding test."');
        $this->command('git tag 1.2.3');

        $this->assertEquals('1.2.3', $this->helper->getTag($this->currentDir));
    }

    /**
     * @2expectedException KevinGH\Elf\Exception\GitException
     */
    public function testGetTagNotRepo()
    {
        $this->helper->getTag($this->currentDir);
    }

    public function testSetDefaultPath()
    {
        $property = $this->property($this->helper, 'path');

        $this->assertEquals($this->currentDir, $property());
    }

    public function testSetDefaultPathInvalid()
    {
        rmdir($dir = $this->dir());

        $this->setExpectedException(
            'InvalidArgumentException',
            "The path \"$dir\" is not a directory path or does not exist."
        );

        $this->helper->setDefaultPath($dir);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage No default Git repository path set.
     */
    public function testGetResolvePathNoDefault()
    {
        $helper = new Git();

        $helper->getCommit();
    }

    private function checkGit(HelperTester $test)
    {
        if (null === $this->command('git --version')) {
            $test->markTestSkipped('Git is not available.');

            return true;
        }

        return false;
    }
}

