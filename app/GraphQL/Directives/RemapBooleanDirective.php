<?php

namespace Nuwave\Lighthouse\Schema\Directives;

use Nuwave\Lighthouse\Support\Contracts\ArgTransformerDirective;

class RemapBooleanDirective extends BaseDirective implements ArgTransformerDirective
{
    /**
     * Directive name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'remapBoolean';
    }

    /**
     * Remove whitespace from the beginning and end of a given input.
     *
     * @param string $argumentValue
     * @return string
     */
    public function transform($argumentValue): string
    {
        if (!is_bool($argumentValue)) {
            return $argumentValue;
        }
        $trueValue = $this->directiveArgValue('true', true);
        $falseValue = $this->directiveArgValue('false', false);

        return $argumentValue ? $trueValue : $falseValue;
    }
}
