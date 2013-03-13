<?php

namespace KevinGH\Elf\Tests\Exception;

use Herrera\PHPUnit\TestCase;
use KevinGH\Elf\Exception\JsonException;

class JsonExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $errors = array(rand());
        $exception = new JsonException('test', $errors);

        $this->assertEquals('test', $exception->getMessage());
        $this->assertEquals($errors, $this->getPropertyValue(
            $exception,
            'errors'
        ));
    }

    public function testErrors()
    {
        $errors = array(rand());
        $exception = JsonException::errors($errors);

        $this->assertEquals($errors, $this->getPropertyValue(
            $exception,
            'errors'
        ));
    }

    /**
     * @depends testConstruct
     */
    public function testGetErrors()
    {
        $errors = array(rand());
        $exception = new JsonException('test', $errors);

        $this->assertEquals($errors, $exception->getErrors());
    }

    public function testInvalidUtf8()
    {
        $exception = JsonException::invalidUtf8();

        $this->assertEquals(
            'The JSON string is not valid UTF-8 string.',
            $exception->getMessage()
        );
    }
}