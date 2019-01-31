<?php

namespace Illuminate\Foundation\Console;

use GraphQL\GraphQL;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Filesystem\Filesystem;
use Nuwave\Lighthouse\GraphQL as LighthouseGraphQL;

class GraphQlSchemaGenerate extends Command
{
    use ConfirmableTrait;

    protected $query = "
    query {
        __schema {
            types {
                kind
                name
                possibleTypes {
                    name
                }
            }
        }
    }";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'graphql:generate-schema
                    {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate GraphQL schema.';

    /**
     * Execute the console command.
     *
     * @param LighthouseGraphQL $graphQL
     * @return void
     */
    public function handle(LighthouseGraphQL $graphQL, Filesystem $storage)
    {
        $this->call('lighthouse:print-schema', ['--write' => null]);
        $schema = $graphQL->prepSchema();
        $result = GraphQL::executeQuery($schema, $this->query, [], null, null);
        $filteredTypes = collect($result->toArray()['data']['__schema']['types'])->filter(function ($type) {
            return $type['possibleTypes'] !== null;
        });
        $output = $result->toArray();
        $output['data']['__schema']['types'] = $filteredTypes->values();
        $storage->put('fragmentTypes.json', json_encode($output['data']));
        $this->info('Schema created successfully.');
    }
}
