<?php

namespace App\GraphQL\Directives;

use App\GraphQL\Execution\MutationExecutor;
use Illuminate\Database\Eloquent\Model;
use Nuwave\Lighthouse\Schema\Directives\UpdateDirective as BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;

class UpdateDirective extends BaseDirective
{
    /**
     * Resolve the field directive.
     *
     * @param FieldValue $fieldValue
     *
     * @return FieldValue
     */
    public function resolveField(FieldValue $fieldValue): FieldValue
    {
        return $fieldValue->setResolver(
            function ($root, array $args): Model {

                /*
                 * @deprecated in favour of @spread
                 */
                if ($this->directiveArgValue('flatten', false)) {
                    $args = reset($args);
                }

                if ($this->directiveArgValue('globalId', false)) {
                    $args['id'] = $this->globalId->decodeId($args['id']);
                }

                $modelClassName = $this->getModelClass();

                /** @var \Illuminate\Database\Eloquent\Model $model */
                $model = new $modelClassName();

                $rename = $this->directiveArgValue('rename', []);
                $rename = collect($rename)->reduce(function ($carry, $item) {
                    [$old, $new] = explode(':', $item);
                    $carry[$old] = $new;
                    return $carry;
                }, []);

                $renameKeys = array_keys($rename);

                $renamedArgs = collect();

                foreach ($args as $argKey => $argValue) {
                    if (in_array($argKey, $renameKeys)) {
                        $renamedArgs->put($rename[$argKey], $argValue);
                    } else {
                        $renamedArgs->put($argKey, $argValue);
                    }
                }

                $executeMutation = function () use ($model, $renamedArgs): Model {
                    return MutationExecutor::executeUpdate($model, $renamedArgs)->refresh();
                };

                return config('lighthouse.transactional_mutations', true)
                    ? $this->databaseManager->connection()->transaction($executeMutation)
                    : $executeMutation();
            }
        );
    }
}
