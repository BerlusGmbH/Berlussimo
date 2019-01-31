<?php

namespace App\GraphQL\Execution;

use App\Models\Traits\ExternalKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Nuwave\Lighthouse\Execution\MutationExecutor as BaseExecutor;
use ReflectionClass;

class MutationExecutor extends BaseExecutor
{
    /**
     * Execute an update mutation.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *         An empty instance of the model that should be updated
     * @param \Illuminate\Support\Collection $args
     *         The corresponding slice of the input arguments for updating this model
     * @param \Illuminate\Database\Eloquent\Relations\Relation|null $parentRelation
     *         If we are in a nested update, we can use this to associate the new model to its parent
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \ReflectionException
     */
    public static function executeUpdate(Model $model, Collection $args, ?Relation $parentRelation = null): Model
    {
        $id = $args->pull('id')
            ?? $args->pull($model->getKeyName());

        if (in_array(ExternalKey::class, class_uses($model)) && $externalKey = $model->getExternalKeyName()) {
            $model = $model->newQuery()->where($externalKey, $id)->first();
            if (!isset($model)) {
                throw (new ModelNotFoundException())->setModel($model, [$id]);
            }
        } else {
            $model = $model->newQuery()->findOrFail($id);
        }

        return MutationExecutor::executeUpdateWithSelectedKey($model, $args, $parentRelation);
    }


    /**
     * @param Model $model
     * @param Collection $args
     * @param Relation|null $parentRelation
     * @return Model
     * @throws \ReflectionException
     */
    protected static function executeUpdateWithSelectedKey(Model $model, Collection $args, ?Relation $parentRelation = null): Model
    {
        $reflection = new ReflectionClass($model);

        [$hasMany, $remaining] = self::partitionArgsByRelationType($reflection, $args, HasMany::class);

        [$morphMany, $remaining] = self::partitionArgsByRelationType($reflection, $remaining, MorphMany::class);

        [$hasOne, $remaining] = self::partitionArgsByRelationType($reflection, $remaining, HasOne::class);

        [$belongsToMany, $remaining] = self::partitionArgsByRelationType($reflection, $remaining, BelongsToMany::class);

        [$morphOne, $remaining] = self::partitionArgsByRelationType($reflection, $remaining, MorphOne::class);

        [$morphToMany, $remaining] = self::partitionArgsByRelationType($reflection, $remaining, MorphToMany::class);

        $model = self::saveModelWithPotentialParent($model, $remaining, $parentRelation);

        $hasMany->each(function (array $nestedOperations, string $relationName) use ($model): void {
            /** @var \Illuminate\Database\Eloquent\Relations\HasMany $relation */
            $relation = $model->{$relationName}();

            (new Collection($nestedOperations))->each(function ($values, string $operationKey) use ($relation): void {
                if ($operationKey === 'create') {
                    self::handleMultiRelationCreate(new Collection($values), $relation);
                }

                if ($operationKey === 'update') {
                    (new Collection($values))->each(function ($singleValues) use ($relation): void {
                        self::executeUpdate($relation->getModel()->newInstance(), new Collection($singleValues), $relation);
                    });
                }

                if ($operationKey === 'delete') {
                    $relation->getModel()::destroy($values);
                }
            });
        });

        $hasOne->each(function (array $nestedOperations, string $relationName) use ($model): void {
            /** @var \Illuminate\Database\Eloquent\Relations\HasOne $relation */
            $relation = $model->{$relationName}();

            (new Collection($nestedOperations))->each(function ($values, string $operationKey) use ($relation): void {
                if ($operationKey === 'create') {
                    self::handleSingleRelationCreate(new Collection($values), $relation);
                }

                if ($operationKey === 'update') {
                    self::executeUpdate($relation->getModel()->newInstance(), new Collection($values), $relation);
                }

                if ($operationKey === 'delete') {
                    $relation->getModel()::destroy($values);
                }
            });
        });

        $morphMany->each(function (array $nestedOperations, string $relationName) use ($model): void {
            /** @var \Illuminate\Database\Eloquent\Relations\MorphMany $relation */
            $relation = $model->{$relationName}();

            (new Collection($nestedOperations))->each(function ($values, string $operationKey) use ($relation): void {
                if ($operationKey === 'create') {
                    self::handleMultiRelationCreate(new Collection($values), $relation);
                }

                if ($operationKey === 'update') {
                    (new Collection($values))->each(function ($singleValues) use ($relation): void {
                        self::executeUpdate($relation->getModel()->newInstance(), new Collection($singleValues), $relation);
                    });
                }

                if ($operationKey === 'delete') {
                    $relation->getModel()::destroy($values);
                }
            });
        });

        $morphOne->each(function (array $nestedOperations, string $relationName) use ($model): void {
            /** @var \Illuminate\Database\Eloquent\Relations\MorphOne $relation */
            $relation = $model->{$relationName}();

            (new Collection($nestedOperations))->each(function ($values, string $operationKey) use ($relation): void {
                if ($operationKey === 'create') {
                    self::handleSingleRelationCreate(new Collection($values), $relation);
                }

                if ($operationKey === 'update') {
                    self::executeUpdate($relation->getModel()->newInstance(), new Collection($values), $relation);
                }

                if ($operationKey === 'delete') {
                    $relation->getModel()::destroy($values);
                }
            });
        });

        $belongsToMany->each(function (array $nestedOperations, string $relationName) use ($model): void {
            /** @var \Illuminate\Database\Eloquent\Relations\BelongsToMany $relation */
            $relation = $model->{$relationName}();

            (new Collection($nestedOperations))->each(function ($values, string $operationKey) use ($relation): void {
                if ($operationKey === 'create') {
                    self::handleMultiRelationCreate(new Collection($values), $relation);
                }

                if ($operationKey === 'update') {
                    (new Collection($values))->each(function ($singleValues) use ($relation): void {
                        self::executeUpdate($relation->getModel()->newInstance(), new Collection($singleValues), $relation);
                    });
                }

                if ($operationKey === 'delete') {
                    $relation->detach($values);
                    $relation->getModel()::destroy($values);
                }

                if ($operationKey === 'connect') {
                    $relation->attach($values);
                }

                if ($operationKey === 'sync') {
                    $relation->sync($values);
                }

                if ($operationKey === 'disconnect') {
                    $relation->detach($values);
                }
            });
        });

        $morphToMany->each(function (array $nestedOperations, string $relationName) use ($model): void {
            /** @var \Illuminate\Database\Eloquent\Relations\MorphToMany $relation */
            $relation = $model->{$relationName}();

            (new Collection($nestedOperations))->each(function ($values, string $operationKey) use ($relation): void {
                if ($operationKey === 'create') {
                    self::handleMultiRelationCreate(new Collection($values), $relation);
                }

                if ($operationKey === 'update') {
                    (new Collection($values))->each(function ($singleValues) use ($relation): void {
                        self::executeUpdate($relation->getModel()->newInstance(), new Collection($singleValues), $relation);
                    });
                }

                if ($operationKey === 'delete') {
                    $relation->detach($values);
                    $relation->getModel()::destroy($values);
                }

                if ($operationKey === 'connect') {
                    $relation->attach($values);
                }

                if ($operationKey === 'sync') {
                    $relation->sync($values);
                }

                if ($operationKey === 'disconnect') {
                    $relation->detach($values);
                }
            });
        });

        return $model;
    }
}