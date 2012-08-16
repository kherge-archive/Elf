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

class JsonTest extends HelperTester
{
    protected $helperClass = 'KevinGH\\Elf\\Json';

    public function testGetName()
    {
        $this->assertEquals('json', $this->helper->getName());
    }

    public function testCheckSyntaxValid()
    {
        $this->assertNull($this->helper->checkSyntax('{}'));
    }

    /**
     * @expectedException Seld\JsonLint\ParsingException
     * @expectedExceptionMessage Parse error on line 1:
     */
    public function testCheckSyntaxInvalid()
    {
        $this->assertNull($this->helper->checkSyntax('{'));
    }

    public function testParseValid()
    {
        $expected = array('rand' => rand());

        $this->assertEquals($expected, $this->helper->parse(json_encode($expected), true));

        $this->assertNull($this->helper->parse('null'));
    }

    /**
     * @expectedException KevinGH\Elf\Exception\JsonException
     * @expectedExceptionMessage The JSON string is not valid UTF-8 string.
     */
    public function testParseInvalidUtf8()
    {
        if ($this->redeclare($this, 'json_last_error', '', 'return JSON_ERROR_UTF8;')) {
            return;
        }

        $this->helper->parse('{');
    }

    /**
     * @expectedException Seld\JsonLint\ParsingException
     * @expectedExceptionMessage Parse error on line 1:
     */
    public function testParseInvalidSyntax()
    {
        $this->helper->parse('{');
    }

    public function testParseFile()
    {
        $expected = array(
            'message' => 'Hello, %s!',
            'name' => 'world'
        );

        $this->assertEquals($expected, $this->helper->parseFile($this->getResource('tests/example.json'), true));
    }

    public function testParseFileNotExist()
    {
        unlink($file = $this->file());

        $this->setExpectedException(
            'InvalidArgumentException',
            "The JSON file path \"$file\" is either not a file or it does not exist."
        );

        $this->helper->parseFile($file);
    }

    public function testParseFileReadError()
    {
        $file = $this->file();

        if ($this->redeclare($this, 'file_get_contents', '', 'return false;')) {
            return;
        }

        $this->setExpectedException(
            'RuntimeException',
            "The JSON file \"$file\" could not be read:"
        );

        $this->helper->parseFile($file);
    }

    public function testValidate()
    {
        $this->assertNull($this->helper->validate(
            $this->helper->parseFile($this->getResource('tests/schema.json')),
            $this->helper->parseFile($this->getResource('tests/example.json'))
        ));
    }

    /**
     * @expectedException KevinGH\Elf\Exception\JsonException
     * @expectedExceptionMessage The JSON string is not valid.
     */
    public function testValidateInvalid()
    {
        $this->assertNull($this->helper->validate(
            $this->helper->parseFile($this->getResource('tests/schema.json')),
            $this->helper->parseFile($this->getResource('tests/example-invalid-schema.json'))
        ));
    }
}

