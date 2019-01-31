<?php


namespace App\Exceptions;


use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

class ValidationException extends \Illuminate\Validation\ValidationException implements RendersErrorsExtensions
{
    /**
     * Returns true when exception message is safe to be displayed to a client.
     *
     * @return bool
     * @api
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Returns string describing a category of the error.
     *
     * Value "graphql" is reserved for errors produced by query parsing or validation, do not use it.
     *
     * @return string
     * @api
     */
    public function getCategory(): string
    {
        return 'validation';
    }

    /**
     * Return the content that is put in the "extensions" part
     * of the returned error.
     *
     * @return array
     */
    public function extensionsContent(): array
    {
        return ['validation' => $this->errors()];
    }
}