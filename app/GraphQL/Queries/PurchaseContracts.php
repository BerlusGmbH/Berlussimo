<?php

namespace App\GraphQL\Queries;

class PurchaseContracts
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
        $builder->active('=', $value);
        return $builder;
    }
}
