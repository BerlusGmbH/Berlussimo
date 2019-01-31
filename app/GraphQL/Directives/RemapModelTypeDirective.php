<?php

namespace Nuwave\Lighthouse\Schema\Directives;

use Nuwave\Lighthouse\Support\Contracts\ArgTransformerDirective;

class RemapModelTypeDirective implements ArgTransformerDirective
{
    /**
     * Directive name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'remapModelType';
    }

    /**
     * @param string $argumentValue
     * @return string
     */
    public function transform($argumentValue): string
    {
        switch ($argumentValue) {
            case 'Property':
                $argumentValue = 'Objekt';
                break;
            case 'Unit':
                $argumentValue = 'Einheit';
                break;
            case 'House':
                $argumentValue = 'Haus';
                break;
            case 'RentalContract':
                $argumentValue = 'Mietvertrag';
                break;
            case 'PurchaseContract':
                $argumentValue = 'Eigentuemer';
                break;
            case 'ConstructionSite':
                $argumentValue = 'Baustelle_ext';
                break;
            case 'AccountingEntity':
                $argumentValue = 'Wirtschaftseinheit';
                break;
        }
        return $argumentValue;
    }
}
