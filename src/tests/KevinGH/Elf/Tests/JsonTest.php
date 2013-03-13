<?php

namespace KevinGH\Elf\Tests;

use Herrera\PHPUnit\TestCase;
use KevinGH\Elf\Exception\JsonException;
use KevinGH\Elf\Json;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

class JsonTest extends TestCase
{
    /** @var Json */
    private $helper;

    public function getSchema()
    {
        return json_decode(<<<SCHEMA
{
    "type": "object",
    "additionalProperties": false,
    "properties": {
        "test": {
            "type": "integer"
        }
    },
    "required": ["test"]
}
SCHEMA
        );
    }

    public function testGetName()
    {
        $this->assertEquals('json', $this->helper->getName());
    }

    public function testCheckSyntax()
    {
        $this->assertNull($this->helper->checkSyntax('{}'));
    }

    public function testCheckSyntaxInvalid()
    {
        $this->setExpectedException('Seld\\JsonLint\\ParsingException');

        $this->helper->checkSyntax('{');
    }

    public function testParse()
    {
        $data = array('rand' => rand());

        $this->assertEquals($data, $this->helper->parse(
            json_encode($data),
            true
        ));
    }

    public function testParseInvalidUtf8()
    {
        $this->setExpectedException('KevinGH\\Elf\\Exception\\JsonException');

        $this->helper->parse('{"bad": \"' . "\xf0\x28\x8c\x28" . '"}');
    }

    public function testParseInvalid()
    {
        $this->setExpectedException('Seld\\JsonLint\\ParsingException');

        $this->helper->parse('{');
    }

    public function testParseFile()
    {
        $file = $this->createFile();

        file_put_contents($file, '{"test": 123}');

        $this->assertEquals(
            array('test' => 123),
            $this->helper->parseFile($file, true)
        );
    }

    public function testParseFileNotExist()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The JSON file path "/does/not/exist" is either not a file or it does not exist.'
        );

        $this->helper->parseFile('/does/not/exist');
    }

    public function testParseFileReadError()
    {
        $root = vfsStream::newDirectory('test');
        $root->addChild(vfsStream::newFile('test.json', 0000));

        vfsStreamWrapper::setRoot($root);

        $this->setExpectedException(
            'RuntimeException',
            'The JSON file "vfs://test/test.json" could not be read:'
        );

        $this->helper->parseFile('vfs://test/test.json');
    }

    public function testValidate()
    {
        $this->assertNull($this->helper->validate(
            $this->getSchema(),
            (object) array('test' => rand())
        ));
    }

    public function testValidateInvalid()
    {
        try {
            $this->helper->validate($this->getSchema(), array());
        } catch (JsonException $exception) {
        }

        $this->assertEquals(
            array('array value found, but a object is required'),
            $exception->getErrors()
        );
    }

    protected function setUp()
    {
        $this->helper = new Json();
    }
}