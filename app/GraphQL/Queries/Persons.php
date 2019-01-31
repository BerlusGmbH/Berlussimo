<?php

namespace App\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Persons
{
    /* Limit result to persons matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function search($builder, $value)
    {
        if (!is_null($value)) {
            $builder->search($value);
        }
        return $builder;
    }

    /* Limit result to persons matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function tenant($builder, $values)
    {
        if (!is_array($values) || count(array_keys($values)) === 0) {
            return $builder;
        }
        $builder->whereHas('mietvertraege', function ($query) use ($values) {
            $query->where(function ($query) use ($values) {
                if (array_key_exists('in', $values) && is_array($values['in'])) {
                    foreach ($values['in'] as $value) {
                        switch ($value['type']) {
                            case "Property":
                                $query->orWhereHas('einheit.haus.objekt', function ($query) use ($value) {
                                    $query->where('id', $value['id']);
                                });
                                break;
                            case "House":
                                $query->orWhereHas('einheit.haus', function ($query) use ($value) {
                                    $query->where('id', $value['id']);
                                });
                                break;
                            case "Unit":
                                $query->orWhereHas('einheit', function ($query) use ($value) {
                                    $query->where('id', $value['id']);
                                });
                        }
                    }
                }
                if (array_key_exists('during', $values) && isset($values['during'])) {
                    $query->active('=', $values['during']);
                }
            });
        });
        return $builder;
    }

    /* Limit result to persons matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function homeOwner($builder, $values)
    {
        if (!is_array($values) || count(array_keys($values)) === 0) {
            return $builder;
        }
        $builder->whereHas('kaufvertraege', function ($query) use ($values) {
            $query->where(function ($query) use ($values) {
                if (array_key_exists('in', $values) && is_array($values['in'])) {
                    foreach ($values['in'] as $value) {
                        switch ($value['type']) {
                            case "Property":
                                $query->orWhereHas('einheit.haus.objekt', function ($query) use ($value) {
                                    $query->where('id', $value['id']);
                                });
                                break;
                            case "House":
                                $query->orWhereHas('einheit.haus', function ($query) use ($value) {
                                    $query->where('id', $value['id']);
                                });
                                break;
                            case "Unit":
                                $query->orWhereHas('einheit', function ($query) use ($value) {
                                    $query->where('id', $value['id']);
                                });
                        }
                    }
                }
                if (array_key_exists('during', $values) && isset($values['during'])) {
                    $query->active('=', $values['during']);
                }
            });
        });
        return $builder;
    }

    /* Limit result to persons matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function employedAt($builder, $values)
    {
        if (is_array($values)) {
            $builder->where(function ($query) use ($values) {
                foreach ($values as $value) {
                    if ($value['type'] === "Partner") {
                        $query->orWhereHas('jobsAsEmployee', function ($query) use ($value) {
                            $query->where('employer_id', $value['id']);
                        });
                    }
                }
            });
        }
        return $builder;
    }

    /* Limit result to persons matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function employedDuring($builder, $value)
    {
        $builder->whereHas('jobsAsEmployee', function ($query) use ($value) {
            $query->active('=', $value);
        });
        return $builder;
    }

    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function resolveScalarTest($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        foreach ($args as $arg) {
            return $arg;
        }
        return $rootValue;
    }
}
