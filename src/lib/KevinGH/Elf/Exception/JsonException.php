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

use InvalidArgumentException;

/**
 * A JSON exception.
 *
 * @author Kevin Herrera <me@kevingh.com>
 */
class JsonException extends InvalidArgumentException
{
    /**
     * The JSON errors.
     *
     * @type array
     */
    private $errors;

    /**
     * Sets the error message and errors.
     *
     * @param string $message The error message.
     * @param array  $errors  The JSON errors.
     */
    public function __construct($message, array $errors = null)
    {
        parent::__construct($message);

        $this->errors = $errors;
    }

    /**
     * Creates an exception for the JSON errors.
     *
     * @param array $errors The JSON errors.
     *
     * @return JsonException The exception.
     */
    public function errors(array $errors)
    {
        return new self('The JSON string is not valid.', $errors);
    }

    /**
     * Returns the JSON errors.
     *
     * @return array The errors.
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Creates an exception for invalid UTF-8 encoding.
     *
     * @return JsonException The exception.
     */
    public function invalidUtf8()
    {
        return new self('The JSON string is not valid UTF-8 string.');
    }
}

