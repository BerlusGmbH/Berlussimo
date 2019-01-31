<?php

namespace App\GraphQL\Interfaces;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Detail
{
    /**
     * @var \Nuwave\Lighthouse\Schema\TypeRegistry
     */
    protected $typeRegistry;

    /**
     * @param \Nuwave\Lighthouse\Schema\TypeRegistry $typeRegistry
     * @return void
     */
    public function __construct(TypeRegistry $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * Decide which GraphQL type a resolved value has.
     *
     * @param mixed $rootValue The value that was resolved by the field. Usually an Eloquent model.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo
     * @return \GraphQL\Type\Definition\Type
     */
    public function resolveType($rootValue, GraphQLContext $context, ResolveInfo $resolveInfo): Type
    {
        $registry = $this->typeRegistry;
        switch ($rootValue->DETAIL_NAME) {
            case 'Email':
                return $registry->get('EMail');
            case 'Fax':
                return $registry->get('Fax');
            case 'Telefon':
            case 'Handy':
                return $registry->get('Phone');
            case 'Hinweis':
                return $registry->get('Note');
            case 'Anschrift':
            case 'Zustellanschrift':
            case 'Verzugsanschrift':
                return $registry->get('PostalAddress');
        }
        return $registry->get('Detail');
    }
}