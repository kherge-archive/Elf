<?php

namespace KevinGH\Elf\Tests;

use Herrera\PHPUnit\TestCase;
use KevinGH\Elf\OpenSsl;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

class OpenSslTest extends TestCase
{
    /** @var OpenSsl */
    private $helper;

    public function testClearBufferedMessages()
    {
        openssl_pkey_get_private('test', 'test');

        $this->helper->clearBufferedMessages();

        $this->assertEmpty(openssl_error_string());
    }

    public function testCreatePrivateKey()
    {
        $type = array_keys($this->helper->getSupportedKeyTypes());
        $type = array_shift($type);

        $this->assertRegExp(
            '/PRIVATE KEY/',
            $this->helper->createPrivateKey('test', $type, 1024)
        );
    }

    public function testCreatePrivateKeyTypeNotSupported()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The private key type "invalid" is not recognized.'
        );

        $this->helper->createPrivateKey(null, 'invalid');
    }

    public function testCreatePrivateKeyFile()
    {
        $file = $this->createFile();

        $this->helper->createPrivateKeyFile($file);

        $this->assertRegExp(
            '/PRIVATE KEY/',
            file_get_contents($file)
        );
    }

    public function testCreatePrivateKeyFileWriteError()
    {
        $root = vfsStream::newDirectory('test');
        $root->addChild(vfsStream::newFile('test.key', 0000));

        vfsStreamWrapper::setRoot($root);

        $this->setExpectedException(
            'RuntimeException',
            'The private key file "vfs://test/test.key" could not be written:'
        );

        $this->helper->createPrivateKeyFile('vfs://test/test.key');
    }

    /**
     * @depends testCreatePrivateKey
     */
    public function testExtractPublicKey()
    {
        $key = $this->helper->createPrivateKey('test');

        $this->assertRegExp(
            '/PUBLIC KEY/',
            $this->helper->extractPublicKey($key, 'test')
        );
    }

    public function testExtractPublicKeyInitError()
    {
        $this->setExpectedException(
            'RuntimeException',
            'The private key could not be processed:'
        );

        $this->helper->extractPublicKey('test');
    }

    /**
     * @depends testCreatePrivateKey
     */
    public function testExtractPublicKeyToFile()
    {
        $key = $this->helper->createPrivateKey('test');
        $file = $this->createFile();

        $this->helper->extractPublicKeyToFile($file, $key, 'test');

        $this->assertRegExp(
            '/PUBLIC KEY/',
            file_get_contents($file)
        );
    }

    public function testExtractPublicKeyToFileWriteError()
    {
        $root = vfsStream::newDirectory('test');
        $root->addChild(vfsStream::newFile('test.key', 0000));

        vfsStreamWrapper::setRoot($root);

        $this->setExpectedException(
            'RuntimeException',
            'The public key file "vfs://test/test.key" could not be written:'
        );

        $key = $this->helper->createPrivateKey('test');

        $this->helper->extractPublicKeyToFile(
            'vfs://test/test.key',
            $key,
            'test'
        );
    }

    public function testGetSupportedKeyTypes()
    {
        $constants = get_defined_constants(true);
        $types = array();

        foreach ($constants['openssl'] as $constant => $value) {
            if (0 === strpos($constant, 'OPENSSL_KEYTYPE')) {
                $key = explode('_KEYTYPE_', $constant);
                $key = strtolower($key[1]);

                $types[$key] = $value;
            }
        }

        $this->assertEquals($types, $this->helper->getSupportedKeyTypes());
    }

    public function testGetName()
    {
        $this->assertEquals('openssl', $this->helper->getName());
    }

    protected function setUp()
    {
        if (false === extension_loaded('openssl')) {
            $this->markTestSkipped('The "openssl" extension is required.');
        }

        $this->helper = new OpenSsl();
    }
}