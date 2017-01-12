<?php

require_once($argv[2]);
require_once($argv[3]);

$parser = new PHP_ParserGenerator();
$file = new SplFileInfo($argv[1]);
$parser->main($file->getPathname());
$content = file_get_contents($file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename('.y') . '.php');
$namespace = str_replace(DIRECTORY_SEPARATOR, '\\', $argv[4]);
$content = str_replace('<?php', "<?php\n\nnamespace App\\Services\\Parser\\{$namespace};\n
use ArrayAccess;
use Exception;
use Relations;\n", $content);
file_put_contents($file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename('.y') . '.php', $content);
unset($parser);