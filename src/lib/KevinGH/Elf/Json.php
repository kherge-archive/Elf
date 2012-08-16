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
use JsonSchema\Validator;
use KevinGH\Elf\Exception\JSONException;
use RuntimeException;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use Symfony\Component\Console\Helper\Helper;

/**
 * Provides support for parsing and validating JSON data.
 *
 * @author Kevin Herrera <me@kevingh.com>
 */
class Json extends Helper
{
    /** {@inheritDoc} */
    public function getName()
    {
        return 'json';
    }

    /**
     * Checks the syntax of the JSON string.
     *
     * @param string $json The JSON string.
     *
     * @throws JsonException  If the JSON string is invalid.
     * @throws ParseException If the JSON string is invalid.
     *
     * @api
     */
    public function checkSyntax($json)
    {
        $parser = new JsonParser;

        if (($result = $parser->lint($json)) instanceof ParsingException) {
            throw $result;
        }
    }

    /**
     * Parses a JSON string.
     *
     * @param string  $json  The JSON string.
     * @param boolean $assoc Convert objects to associate arrays?
     * @param integer $depth The maximum recursion depth.
     *
     * @api
     */
    public function parse($json, $assoc = false, $depth = 512)
    {
        if (null === ($data = json_decode($json, $assoc, $depth))) {
            if (JSON_ERROR_UTF8 === json_last_error()) {
                throw JsonException::invalidUtf8();
            }

            $this->checkSyntax($json);
        }

        return $data;
    }

    /**
     * Parses a JSON file.
     *
     * @param string  $file  The JSON file.
     * @param boolean $assoc Convert objects to associate arrays?
     * @param integer $depth The maximum recursion depth.
     *
     * @throws InvalidArgumentException If the JSON file does not exist.
     * @throws RuntimeException         If the JSON file could not be read.
     *
     * @api
     */
    public function parseFile($file, $assoc = false, $depth = 512)
    {
        if (false === is_file($file)) {
            throw new InvalidArgumentException(sprintf(
                'The JSON file path "%s" is either not a file or it does not exist.',
                $file
            ));
        }

        if (false === ($contents = @ file_get_contents($file))) {
            $error = error_get_last();

            throw new RuntimeException(sprintf(
                'The JSON file "%s" could not be read: %s',
                $file,
                $error['message']
            ));
        }

        return $this->parse($contents, $assoc, $depth);
    }

    /**
     * Validates the JSON data.
     *
     * @param object $schema The JSON schema.
     * @param mixed  $json   The JSON data.
     *
     * @throws JSONException If the JSON data is invalid.
     *
     * @api
     */
    public function validate($schema, $json)
    {
        $validator = new Validator;

        $validator->check($json, $schema);

        if (false === $validator->isValid()) {
            $errors = array();

            foreach ($validator->getErrors() as $error) {
                $errors[] = (empty($error['property']) ? '' : $error['property'] . ': ')
                          . $error['message'];
            }

            throw JSONException::errors($errors);
        }
    }
}

