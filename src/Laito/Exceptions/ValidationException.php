<?php
namespace Laito\Exceptions;

use \Exception as Exception;

class ValidationException extends Exception
{
    /**
     * Errors array
     *
     * @var array
     */
    private $errors = [];

    /**
     * Overwrite default exception constructor
     *
     * @param string $message Message
     * @param int $code Code
     * @param Exception|null $previous Previous exception
     * @param array $errors Array of validation errors
     */
    public function __construct($message, $code = 0, array $errors = [], Exception $previous = null)
    {
        // Save erors
        $this->errors = $errors;

        // Construct
        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets the validation errors
     *
     * @return array Validation errors
     */
    public function getErrors()
    {
        return $this->errors;
    }
}