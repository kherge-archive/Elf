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

class OpenSslTest extends HelperTester
{
    protected $helperClass = 'KevinGH\\Elf\\OpenSsl';

    public function testSupportedKeyTypes()
    {
        if ($this->checkSupport($this, 'openssl')) {
            return;
        }

        $constants = get_defined_constants(true);
        $keyTypes = array();

        foreach ($constants['openssl'] as $constant => $value) {
            if (0 === strpos($constant, 'OPENSSL_KEYTYPE')) {
                $key = explode('_KEYTYPE_', $constant);
                $key = strtolower($key[1]);

                $keyTypes[$key] = $value;
            }
        }

        $this->assertEquals($keyTypes, $this->helper->getSupportedKeyTypes());
    }

    public function testClearBufferedMessages()
    {
        if ($this->checkSupport($this, 'openssl')) {
            return;
        }

        @ openssl_pkey_new(array(
            'private_key_type' => 'invalid'
        ));

        $this->helper->clearBufferedMessages();

        $this->assertEmpty(openssl_error_string());
    }

    public function testCreatePrivateKey()
    {
        if ($this->checkSupport($this, 'openssl')) {
            return;
        }

        $types = $this->helper->getSupportedKeyTypes();
        $type = 'dsa';
        $bits = 640;

        $private = $this->helper->createPrivateKey('phpunit', $type, $bits);

        $resource = openssl_pkey_get_private($private, 'phpunit');
        $details = openssl_pkey_get_details($resource);

        openssl_free_key($resource);

        $this->assertEquals($types[$type], $details['type']);
        $this->assertEquals($bits, $details['bits']);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The private key type "test" is not recognized.
     */
    public function testCreatePrivateKeyUnsupportedType()
    {
        if ($this->checkSupport($this, 'openssl')) {
            return;
        }

        $this->helper->createPrivateKey('phpunit', 'test');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The private key could not be created:
     */
    public function testCreatePrivateKeyNewError()
    {
        if ($this->checkSupport($this, 'openssl')) {
            return;
        }

        if ($this->redeclare($this, 'openssl_pkey_new', '', 'return false;')) {
            return;
        }

        $this->helper->createPrivateKey();
    }
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The private key could not be exported:
     */
    public function testCreatePrivateKeyExportError()
    {
        if ($this->checkSupport($this, 'openssl')) {
            return;
        }

        if ($this->redeclare($this, 'openssl_pkey_export', '$a, &$b, $c', 'return false;')) {
            return;
        }

        $this->helper->createPrivateKey();
    }

    public function testCreatePrivateKeyFile()
    {
        if ($this->checkSupport($this, 'openssl')) {
            return;
        }

        $file = $this->file();

        $this->helper->createPrivateKeyFile($file);

        $this->assertFileExists($file);
        $this->assertRegExp('/PRIVATE KEY/', file_get_contents($file));
    }

    public function testCreatePrivateKeyFileWriteError()
    {
        if ($this->checkSupport($this, 'openssl')) {
            return;
        }

        if ($this->redeclare($this, 'file_put_contents', '', 'return false;')) {
            return;
        }

        $file = $this->file();

        $this->setExpectedException(
            'RuntimeException',
            "The private key file \"$file\" could not be written:"
        );

        $this->helper->createPrivateKeyFile($file);
    }

    public function testExtractPublicKey()
    {
        if ($this->checkSupport($this, 'openssl')) {
            return;
        }

        $private = $this->helper->createPrivateKey('phpunit');
        $public = $this->helper->extractPublicKey($private, 'phpunit');

        $resource = openssl_pkey_get_private($private, 'phpunit');
        $details = openssl_pkey_get_details($resource);

        openssl_free_key($resource);

        $this->assertEquals($details['key'], $public);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The private key could not be processed:
     */
    public function testExtractPublicKeyInvalidPrivateKey()
    {
        if ($this->checkSupport($this, 'openssl')) {
            return;
        }

        $this->helper->extractPublicKey('test');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The details of the private key could not be extracted:
     */
    public function testExtractPublicKeyExtractError()
    {
        if ($this->checkSupport($this, 'openssl')) {
            return;
        }

        $private = $this->helper->createPrivateKey('phpunit');

        if ($this->redeclare($this, 'openssl_pkey_get_details', '', 'return false;')) {
            return;
        }

        $this->helper->extractPublicKey($private, 'phpunit');
    }

    public function testExtractPublicKeyToFile()
    {
        if ($this->checkSupport($this, 'openssl')) {
            return;
        }

        $file = $this->file();

        $private = $this->helper->createPrivateKey('phpunit');

        $this->helper->extractPublicKeyToFile($file, $private, 'phpunit');

        $this->assertFileExists($file);
        $this->assertRegExp('/PUBLIC KEY/', file_get_contents($file));
    }

    public function testExtractPublicKeyToFileWriteError()
    {
        if ($this->checkSupport($this, 'openssl')) {
            return;
        }

        $file = $this->file();

        if ($this->redeclare($this, 'file_put_contents', '', 'return false;')) {
            return;
        }

        $private = $this->helper->createPrivateKey('phpunit');

        $this->setExpectedException(
            'RuntimeException',
            "The public key file \"$file\" could not be written:"
        );

        $this->helper->extractPublicKeyToFile($file, $private, 'phpunit');
    }

    public function testGetName()
    {
        $this->assertEquals('openssl', $this->helper->getName());
    }
}

