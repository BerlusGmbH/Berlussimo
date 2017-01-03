<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use PHP_LexerGenerator;
use PHP_ParserGenerator;

class GenerateSearchParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:parser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Parser and Lexer used to parse query strings.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        chdir(base_path('vendor/smarty/smarty-lexer/'));
        require_once(base_path('vendor/smarty/smarty-lexer/LexerGenerator.php'));
        require_once(base_path('vendor/smarty/smarty-lexer/ParserGenerator.php'));
        chdir(app_path('Services/SearchParser'));
        new PHP_LexerGenerator(resource_path('parser/SearchLexer.plex'));
        $content = file_get_contents(resource_path('parser/SearchLexer.php'));
        $content = str_replace('/isS', '/isSu', $content);
        file_put_contents(resource_path('parser/SearchLexer.php'), $content);
        $parser = new PHP_ParserGenerator();
        $parser->main(resource_path('parser/SearchParser.y'));
        $content = file_get_contents(resource_path('parser/SearchParser.php'));
        $content = str_replace('<?php', "<?php\n\nnamespace App\\Services\\SearchParser;\n\nuse ArrayAccess;\nuse Exception;\n", $content);
        file_put_contents(resource_path('parser/SearchParser.php'), $content);
        rename(resource_path('parser/SearchLexer.php'), app_path('Services/SearchParser/SearchLexer.php'));
        rename(resource_path('parser/SearchParser.php'), app_path('Services/SearchParser/SearchParser.php'));
        unlink(resource_path('parser/SearchParser.out'));
    }
}