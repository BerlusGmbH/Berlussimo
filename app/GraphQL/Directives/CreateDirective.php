<?php

namespace App\GraphQL\Directives;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Exceptions\DirectiveException;
use Nuwave\Lighthouse\Schema\Directives\CreateDirective as BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CreateDirective extends BaseDirective implements FieldResolver
{
    /**
     * Resolve the field directive.
     *
     * @param \Nuwave\Lighthouse\Schema\Values\FieldValue $fieldValue
     * @return \Nuwave\Lighthouse\Schema\Values\FieldValue
     * @throws DirectiveException
     */
    public function resolveField(FieldValue $fieldValue): FieldValue
    {
        parent::resolveField($fieldValue);

        $resolver = $fieldValue->getResolver();

        $fieldValue->setResolver(function ($root, array $args, GraphQLContext $context, ResolveInfo $info) use ($resolver) {
            $rename = $this->directiveArgValue('rename', []);
            $rename = collect($rename)->reduce(function ($carry, $item) {
                [$old, $new] = explode(':', $item);
                $carry[$old] = $new;
                return $carry;
            }, []);

            $flatten = $this->directiveArgValue('flatten', false);
            $args = $flatten
                ? reset($args)
                : $args;

            $renameKeys = array_keys($rename);

            $renamedArgs = collect();

            foreach ($args as $argKey => $argValue) {
                if (in_array($argKey, $renameKeys)) {
                    $renamedArgs->put($rename[$argKey], $argValue);
                } else {
                    $renamedArgs->put($argKey, $argValue);
                }
            }

            return $resolver($root, $renamedArgs->all(), $context, $info);
        });

        return $fieldValue;
    }
}
