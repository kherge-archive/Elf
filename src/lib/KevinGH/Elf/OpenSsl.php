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
use RuntimeException;
use Symfony\Component\Console\Helper\Helper;

/**
 * Creates private and public keys.
 *
 * @author Kevin Herrera <me@kevingh.com>
 */
class OpenSsl extends Helper
{
    /**
     * The supported key types.
     *
     * @type array
     */
    private $keyTypes = array();

    /**
     * Gets the list of supported key types.
     */
    public function __construct()
    {
        $constants = get_defined_constants(true);

        foreach ($constants['openssl'] as $constant => $value) {
            if (0 === strpos($constant, 'OPENSSL_KEYTYPE')) {
                $key = explode('_KEYTYPE_', $constant);
                $key = strtolower($key[1]);

                $this->keyTypes[$key] = $value;
            }
        }
    }

    /**
     * Clears buffered OpenSSL error messages.
     */
    public function clearBufferedMessages()
    {
        while (openssl_error_string()) {
            // clear out buffered messages
        }
    }

    /**
     * Creates a new private key.
     *
     * @param null|string $passphrase The passphrase.
     * @param null|string $type       The key type.
     * @param null|string $bits       The number of bits.
     *
     * @return string The new private key.
     *
     * @throws InvalidArgumentException If the private key type is not recognized.
     * @throws RuntimeException         If the private key could not be created.
     *
     * @api
     */
    public function createPrivateKey($passphrase = null, $type = null, $bits = null)
    {
        $options = array();

        if ($type) {
            $type = strtolower($type);

            if (false === isset($this->keyTypes[$type])) {
                throw new InvalidArgumentException(sprintf(
                    'The private key type "%s" is not recognized.',
                    $type
                ));
            }

            $options['private_key_type'] = $this->keyTypes[$type];
        }

        if ($bits) {
            $options['private_key_bits'] = $bits;
        }

        $this->clearBufferedMessages();

        if (false === ($resource = openssl_pkey_new($options))) {
            throw new RuntimeException(sprintf(
                'The private key could not be created: %s',
                openssl_error_string()
            ));
        }

        if (false === openssl_pkey_export($resource, $private, $passphrase)) {
            throw new RuntimeException(sprintf(
                'The private key could not be exported: %s',
                openssl_error_string()
            ));
        }

        openssl_free_key($resource);

        return $private;
    }

    /**
     * Creates a private key and saves it to a file.
     *
     * @param string      $file       The file path.
     * @param null|string $passphrase The passphrase.
     * @param null|string $type       The key type.
     * @param null|string $bits       The number of bits.
     *
     * @throws RuntimeException If the file could not be written.
     *
     * @api
     */
    public function createPrivateKeyFile($file, $passphrase = null, $type = null, $bits = null)
    {
        $private = $this->createPrivateKey($passphrase, $type, $bits);

        if (false === @ file_put_contents($file, $private)) {
            $error = error_get_last();

            throw new RuntimeException(sprintf(
                'The private key file "%s" could not be written: %s',
                $file,
                $error['message']
            ));
        }
    }

    /**
     * Extracts the public key from the private key.
     *
     * @param string      $private    The private key.
     * @param null|string $passphrase The passphrase.
     *
     * @return string The public key.
     *
     * @throws RuntimeException If the public key could not be extracted.
     *
     * @api
     */
    public function extractPublicKey($private, $passphrase = null)
    {
        $this->clearBufferedMessages();

        if (false === ($resource = openssl_pkey_get_private($private, $passphrase))) {
            throw new RuntimeException(sprintf(
                'The private key could not be processed: %s',
                openssl_error_string()
            ));
        }

        if (false === ($details = openssl_pkey_get_details($resource))) {
            throw new RuntimeException(sprintf(
                'The details of the private key could not be extracted: %s',
                openssl_error_string()
            ));
        }

        openssl_free_key($resource);

        return $details['key'];
    }

    /**
     * Extracts the public key and saves it to a file.
     *
     * @param string      $file       The file path.
     * @param string      $private    The private key.
     * @param null|string $passphrase The passphrase.
     *
     * @throws RuntimeException If the file could not be written.
     *
     * @api
     */
    public function extractPublicKeyToFile($file, $private, $passphrase = null)
    {
        $public = $this->extractPublicKey($private, $passphrase);

        if (false === @ file_put_contents($file, $public)) {
            $error = error_get_last();

            throw new RuntimeException(sprintf(
                'The public key file "%s" could not be written: %s',
                $file,
                $error['message']
            ));
        }
    }

    /**
     * Returns the list of supported key types.
     *
     * @return array The key types.
     *
     * @api
     */
    public function getSupportedKeyTypes()
    {
        return $this->keyTypes;
    }

    /** {@inheritDoc} */
    public function getName()
    {
        return 'openssl';
    }
}

