<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use PHP_LexerGenerator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class GenerateSearchParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:generate';

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
        chdir(resource_path('parser/'));
        foreach ($iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(resource_path('parser/'), RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            if ($file->getExtension() === 'plex') {
                $this->info('Generating Lexer: ' . $file->getFilename());
                $lex = new PHP_LexerGenerator($file->getPathname());
                $content = file_get_contents($file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename('.plex') . '.php');
                $content = str_replace('/isS', '/isSu', $content);
                file_put_contents($file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename('.plex') . '.php', $content);
                unset($lex);
            }
        }
        chdir(base_path('vendor/smarty/smarty-lexer/'));
        foreach ($iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(resource_path('parser/'), RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            if ($file->getExtension() === 'y') {
                $this->info('Generating Parser: ' . $file->getFilename());
                passthru('php "' . resource_path('parser' . DIRECTORY_SEPARATOR . 'script.php') . '" "' . $file->getPathname() . '" "' . base_path('vendor/smarty/smarty-lexer/LexerGenerator.php') . '" "' . base_path('vendor/smarty/smarty-lexer/ParserGenerator.php') . '" "' . $iterator->getSubPath() . '"');
            }
        }
        foreach ($iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(resource_path('parser/'), RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            if ($file->getExtension() === 'php' && $file->getFilename() !== 'script.php') {
                if(!file_exists(app_path('Services' . DIRECTORY_SEPARATOR . 'Parser' . DIRECTORY_SEPARATOR . $iterator->getSubPath()))) {
                    mkdir(app_path('Services' . DIRECTORY_SEPARATOR . 'Parser' . DIRECTORY_SEPARATOR . $iterator->getSubPath()), 0777, true);
                }
                rename($file->getPathname(), app_path('Services' . DIRECTORY_SEPARATOR . 'Parser' . DIRECTORY_SEPARATOR . $iterator->getSubPathname()));
            }
        }
    }
}