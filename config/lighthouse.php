<?php

use GraphQL\Error\Debug;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;

return [

    /*
    |--------------------------------------------------------------------------
    | Route configuration
    |--------------------------------------------------------------------------
    |
    | Controls the HTTP route that your GraphQL server responds to.
    | You may set `route` => false, to disable the default route
    | registration and take full control.
    |
    */
    'route' => [
        /*
         * The URI the endpoint responds to, e.g. mydomain.com/graphql.
         */
        'uri' => 'graphql',
        /*
         * Lighthouse creates a named route for convenient URL generation and redirects.
         */
        'name' => 'graphql',
        'middleware' => [
            \App\Http\Middleware\EncryptCookies::class,
            \App\Http\Middleware\PreAuthenticate::class . ':api,web',
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Declaration
    |--------------------------------------------------------------------------
    |
    | This is a path that points to where your GraphQL schema is located
    | relative to the app path. You should define your entire GraphQL
    | schema in this file (additional files may be imported).
    |
    */
    'schema' => [
        'register' => base_path('routes/graphql/schema.graphqls'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Cache
    |--------------------------------------------------------------------------
    |
    | A large part of schema generation consists of parsing and AST manipulation.
    | This operation is very expensive, so it is highly recommended to enable
    | caching of the final schema to optimize performance of large schemas.
    |
    */
    'cache' => [
        'enable' => env('LIGHTHOUSE_CACHE_ENABLE', true),
        'key' => env('LIGHTHOUSE_CACHE_KEY', 'lighthouse-schema'),
        'ttl' => env('LIGHTHOUSE_CACHE_TTL', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    | These are the default namespaces where Lighthouse looks for classes
    | that extend functionality of the schema. You may pass either a string
    | or an array, they are tried in order and the first match is used.
    |
    */
    'namespaces' => [
        'models' => ['App', 'App\\Models'],
        'queries' => 'App\\GraphQL\\Queries',
        'mutations' => 'App\\GraphQL\\Mutations',
        'subscriptions' => 'App\\GraphQL\\Subscriptions',
        'interfaces' => 'App\\GraphQL\\Interfaces',
        'unions' => 'App\\GraphQL\\Unions',
        'scalars' => 'App\\GraphQL\\Scalars',
        'directives' => ['App\\GraphQL\\Directives'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Control how Lighthouse handles security related query validation.
    | This configures the options from http://webonyx.github.io/graphql-php/security/
    |
    */
    'security' => [
        'max_query_complexity' => QueryComplexity::DISABLED,
        'max_query_depth' => QueryDepth::DISABLED,
        'disable_introspection' => DisableIntrospection::DISABLED,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Limits the maximum "count" that users may pass as an argument
    | to fields that are paginated with the @paginate directive.
    | A setting of "null" means the count is unrestricted.
    |
    */
    'paginate_max_count' => null,
    /*
    |--------------------------------------------------------------------------
    | Pagination Amount Argument
    |--------------------------------------------------------------------------
    |
    | Set the name to use for the generated argument on paginated fields
    | that controls how many results are returned.
    | This setting will be removed in v5.
    |
    */
    'pagination_amount_argument' => 'first',


    /*
    |--------------------------------------------------------------------------
    | Debug
    |--------------------------------------------------------------------------
    |
    | Control the debug level as described in http://webonyx.github.io/graphql-php/error-handling/
    | Debugging is only applied if the global Laravel debug config is set to true.
    |
    */
    'debug' => Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE,

    /*
    |--------------------------------------------------------------------------
    | Error Handlers
    |--------------------------------------------------------------------------
    |
    | Register error handlers that receive the Errors that occur during execution and
    | handle them. You may use this to log, filter or format the errors.
    | The classes must implement Nuwave\Lighthouse\Execution\ErrorHandler
    |
    */
    'error_handlers' => [
        \App\Exceptions\GraphQLHandler::class,
        \Nuwave\Lighthouse\Execution\ExtensionErrorHandler::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Global ID
    |--------------------------------------------------------------------------
    |
    | The name that is used for the global id field on the Node interface.
    | When creating a Relay compliant server, this must be named "id".
    |
    */
    'global_id_field' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Batched Queries
    |--------------------------------------------------------------------------
    |
    | GraphQL query batching means sending multiple queries to the server in one request,
    | You may set this flag to process/deny batched queries.
    |
    */
    'batched_queries' => true,

    /*
    |--------------------------------------------------------------------------
    | Transactional Mutations
    |--------------------------------------------------------------------------
    |
    | Sets default setting for transactional mutations.
    | You may set this flag to have @create|@update mutations transactional or not.
    |
    */
    'transactional_mutations' => true,

    /*
    |--------------------------------------------------------------------------
    | GraphQL Subscriptions
    |--------------------------------------------------------------------------
    |
    | Here you can define GraphQL subscription "broadcasters" and "storage" drivers
    | as well their required configuration options.
    |
     */
    'subscriptions' => [
        /*
         * Determines if broadcasts should be queued by default.
         */
        'queue_broadcasts' => env('LIGHTHOUSE_QUEUE_BROADCASTS', true),

        /*
         * Default subscription storage.
         *
         * NOTE: Any laravel supported cache driver options are available here.
         */
        'storage' => env('LIGHTHOUSE_SUBSCRIPTION_STORAGE', 'redis'),

        /*
         * Default subscription broadcaster.
         */
        'broadcaster' => env('LIGHTHOUSE_BROADCASTER', 'pusher'),

        /*
         * Subscription broadcasting drivers w/ config options.
         */
        'broadcasters' => [
            'log' => [
                'driver' => 'log',
            ],
            'pusher' => [
                'driver' => 'pusher',
                'routes' => \App\GraphQL\RoutesRegister::class . '@register',
            ],
        ],
    ],
];
