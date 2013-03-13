<?php

namespace KevinGH\Elf\Tests\Exception;

use Herrera\PHPUnit\TestCase;
use KevinGH\Elf\Exception\GitException;
use Symfony\Component\Process\Process;

class GitExceptionTest extends TestCase
{
    public function testProcess()
    {
        $process = $this->getMockBuilder('Symfony\Component\Process\Process')
                        ->disableOriginalConstructor()
                        ->getMock();

        $process->expects($this->once())
                ->method('getErrorOutput')
                ->will($this->returnValue('error'));

        $process->expects($this->once())
                ->method('getOutput')
                ->will($this->returnValue('output'));

        $exception = GitException::process($process);

        $this->assertEquals('outputerror', $exception->getMessage());
    }
}