<?php

namespace App\GraphQL\Mutations;

use App\Models\Details;
use App\Services\PhoneLocator;
use GraphQL\Type\Definition\ResolveInfo;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Dial
{
    protected $locator;

    public function __construct(PhoneLocator $locator)
    {
        $this->locator = $locator;
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
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $id = $args['id'];

        $phone = Details::where('DETAIL_ID', $id)->first();

        if ($this->locator->workplaceHasPhone() && isset($phone)) {
            $client = new Client();
            $client->get($this->locator->url(trim($phone->DETAIL_INHALT)));
            return true;
        } else {
            throw new InvalidArgumentException('No Phone is known for your workplace.');
        }
    }
}
