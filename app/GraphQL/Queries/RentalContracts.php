<?php

namespace App\GraphQL\Queries;

class RentalContracts
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
    public function unitPartOf($builder, $values)
    {
        if (is_array($values)) {
            $builder->where(function ($query) use ($values) {
                foreach ($values as $value) {
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
    public function active($builder, $value)
    {
        if (
            is_array($value)
            && isset($value['direction'])
            && isset($value['date'])
        ) {
            switch ($value['direction']) {
                case "EQ":
                    $builder->active('=', $value['date']);
                    break;
                case "LT":
                    $builder->active('<', $value['date']);
                    break;
                case "LTE":
                    $builder->active('<=', $value['date']);
                    break;
                case "GT":
                    $builder->active('>', $value['date']);
                    break;
                case "GTE":
                    $builder->active('>=', $value['date']);
                    break;
            }
        }
        return $builder;
    }

    public function inactive($builder, $value)
    {
        if (
            is_array($value)
            && isset($value['direction'])
            && isset($value['date'])
        ) {
            switch ($value['direction']) {
                case "EQ":
                    $builder->inactive('=', $value['date']);
                    break;
                case "LT":
                    $builder->inactive('<', $value['date']);
                    break;
                case "LTE":
                    $builder->inactive('<=', $value['date']);
                    break;
                case "GT":
                    $builder->inactive('>', $value['date']);
                    break;
                case "GTE":
                    $builder->inactive('>=', $value['date']);
                    break;
            }
        }
        return $builder;
    }

    /* Limit result to persons matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function order($builder, $value, $test)
    {
        switch ($value) {
            case "MOVING_IN":
                $builder->movingInOrder('=', $value);
                break;
            case "MOVED_IN":
                $builder->movedInOrder('=', $value);
                break;
            case "MOVING_OUT":
                $builder->movingOutOrder('=', $value);
                break;
            case "MOVED_OUT":
                $builder->movedOutOrder('=', $value);
                break;
        }
        return $builder;
    }
}
