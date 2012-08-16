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

use PHPUnit_Framework_TestCase;

class JsonExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $errors = array('rand' => rand());
        $exception = new JsonException('Test message.', $errors);

        $this->assertEquals('Test message.', $exception->getMessage());
        $this->assertEquals($errors, $exception->getErrors());
    }

    public function testErrors()
    {
        $errors = array('rand' => rand());
        $exception = JsonException::errors($errors);

        $this->assertEquals('The JSON string is not valid.', $exception->getMessage());
        $this->assertEquals($errors, $exception->getErrors());
    }

    public function testGetErrors()
    {
        $exception = new JsonException('');

        $this->assertNull($exception->getErrors());
    }

    public function testInvalidUtf8()
    {
        $exception = JsonException::invalidUtf8();

        $this->assertEquals('The JSON string is not valid UTF-8 string.', $exception->getMessage());
    }
}

