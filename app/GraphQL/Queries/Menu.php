<?php


namespace App\GraphQL\Queries;


use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Menu
{
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $content = "";

        if ($args['module']) {
            switch ($args['module']) {
                case "MAIN":
                    $content = response()->view('api.menus.main')->content();
                    break;
                case "INVOICE":
                    $content = response()->view('api.menus.invoice')->content();
                    break;
                case "RENTAL_CONTRACT":
                    $content = response()->view('api.menus.rentalcontract')->content();
                    break;
            }
        }
        return $content;
    }
}
