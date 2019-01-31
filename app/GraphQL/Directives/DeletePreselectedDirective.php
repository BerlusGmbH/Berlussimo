<?php

namespace Nuwave\Lighthouse\Schema\Directives;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class DeletePreselectedDirective extends BaseDirective implements FieldResolver
{
    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name(): string
    {
        return 'deletePreselected';
    }

    /**
     * Resolve the field directive.
     *
     * @param \Nuwave\Lighthouse\Schema\Values\FieldValue $fieldValue
     * @return \Nuwave\Lighthouse\Schema\Values\FieldValue
     * @throws \Nuwave\Lighthouse\Exceptions\DefinitionException
     */
    public function resolveField(FieldValue $fieldValue): FieldValue
    {
        return $fieldValue->setResolver(
            function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) {
                $models = $resolveInfo
                    ->argumentSet
                    ->enhanceBuilder(
                        $this->getModelClass()::query(),
                        $this->directiveArgValue('scopes', [])
                    )
                    ->get();

                $count = 0;

                foreach ($models as $model) {
                    if ($model->delete()) {
                        $count++;
                    }
                }

                return $count;
            }
        );
    }
}
