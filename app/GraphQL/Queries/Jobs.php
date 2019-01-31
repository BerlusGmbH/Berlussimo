<?php

namespace App\GraphQL\Queries;

class Jobs
{

    /* Limit result to persons matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function employer($builder, $values)
    {
        if (is_array($values)) {
            $builder->where(function ($query) use ($values) {
                foreach ($values as $value) {
                    if ($value['type'] === "Partner") {
                        $query->orWhereHas('employer', function ($query) use ($value) {
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
