<?php

namespace App\GraphQL\Scalars;

use Carbon\Carbon;
use Exception;
use GraphQL\Error\Error;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;

class DateTime extends ScalarType
{
    /**
     * Serialize an internal value, ensuring it is a valid datetime string.
     *
     * @param  \Carbon\Carbon|string $value
     * @return string
     * @throws Error
     */
    public function serialize($value): ?string
    {
        if ($value === "0000-00-00 00:00:00" || $value === "9999-12-31 23:59:59") {
            return null;
        }

        if (!$value instanceof Carbon) {
            $value = $this->tryParsingDateTime($value, InvariantViolation::class);
        }

        if ($value->year < 1 || $value->year === 9999) {
            return null;
        }

        return $value->toDateTimeString();
    }

    /**
     * Try to parse the given value into a Carbon instance, throw if it does not work.
     *
     * @param  mixed $value
     * @param  string $exceptionClass
     * @return \Carbon\Carbon
     *
     * @throws \GraphQL\Error\InvariantViolation|Error
     */
    private function tryParsingDateTime($value, string $exceptionClass): Carbon
    {
        try {
            return Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $value);
        } catch (Exception $e) {
            throw new $exceptionClass(
                Utils::printSafeJson(
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Parse a externally provided variable value into a Carbon instance.
     *
     * @param  mixed $value
     * @return \Carbon\Carbon
     * @throws Error
     */
    public function parseValue($value): Carbon
    {
        return $this->tryParsingDateTime($value, Error::class);
    }

    /**
     * Parse a literal provided as part of a GraphQL query string into a Carbon instance.
     *
     * @param  \GraphQL\Language\AST\Node $valueNode
     * @param  mixed[]|null $variables
     * @return \Carbon\Carbon
     *
     * @throws \GraphQL\Error\Error
     */
    public function parseLiteral($valueNode, ?array $variables = null): Carbon
    {
        if (!$valueNode instanceof StringValueNode) {
            throw new Error(
                'Query error: Can only parse strings got: ' . $valueNode->kind,
                [$valueNode]
            );
        }

        return $this->tryParsingDateTime($valueNode->value, Error::class);
    }
}
