<?php

namespace App\GraphQL\Mutations;

use App\Models\Credential;
use App\Models\Person;
use GraphQL\Type\Definition\ResolveInfo;
use Hash;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class UpdateCredential
{
    /**
     * Return a value for the field.
     *
     * @param  null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[] $args The arguments that were passed into the field.
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param  \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $personId = $args['personId'];
        $password = $args['password'];
        $enabled = $args['enabled'];

        $person = Person::findOrFail($personId);

        if (isset($password)) {
            if ($person->credential === null) {
                $c = new Credential();
            } else {
                $c = $person->credential()->withTrashed()->first();
            }
            $c->forceFill(['password' => Hash::make($password)]);
            $person->credential()->save($c);
        }
        if (isset($enabled)) {
            if ($enabled) {
                $person->credential()->restore();
            } else {
                $person->credential()->delete();
            }
        }
        return $person;
    }
}
