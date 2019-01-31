<?php

namespace App\GraphQL\Unions;


class Notification
{
    public function resolveType($value)
    {
        return collect(explode('\\', $value->type))->last() . 'Notification';
    }
}
