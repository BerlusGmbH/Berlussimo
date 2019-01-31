<?php

namespace App\GraphQL\Mutations;

use App\Models\Person;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class MergePersons
{
    use DispatchesJobs;

    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $personIds = $args['ids'];
        $personInput = $args['person'];

        $attributes = collect($personInput)->keys()->reduce(function ($carry, $key) use ($personInput) {
            switch ($key) {
                case "lastName":
                    $carry["name"] = $personInput[$key];
                    break;
                case "firstName":
                    $carry["first_name"] = $personInput[$key];
                    break;
                case "gender":
                    $carry["sex"] = $personInput[$key];
                    break;
                default:
                    $carry[$key] = $personInput[$key];
            }
            return $carry;
        }, []);

        $first = Person::findOrFail($personIds[0]);
        $second = Person::findOrFail($personIds[1]);

        return $this->dispatch(new \App\Jobs\MergePersons($attributes, $first, $second));
    }
}
