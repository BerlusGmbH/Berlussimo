<?php

define('BERLUSSIMO_PATH', __DIR__ . '/../../../');
define('SCHEMA', 'berlussimo_dev');
define('DB_USER', 'root');
define('DB_PASSWORD', 'hausbau7681');
define('VIEWS', __DIR__ . '/views');
define('CACHE', __DIR__ . '/cache');

require __DIR__ . '/../../../vendor/autoload.php';

use Philo\Blade\Blade;


$info = getInfoFromDatabase();

$relationships = getRelationshipsFromCode();

findUsages($info);

renderSchemaInfo($info, '../berlussimo_schema.lyx');

renderSchemaGraph($relationships, '../database_relationships.dot');

function renderSchemaGraph($relationships, $fname)
{
    $blade = new Blade(VIEWS, CACHE);
    $dot_relationships = $blade->view()->make('relationships', ['relationships' => $relationships])->render();
    $fh = fopen($fname, 'w');
    fwrite($fh, $dot_relationships);
    fclose($fh);
}

function renderSchemaInfo($info, $fname)
{
    $blade = new Blade(VIEWS, CACHE);
    $lyx_schema = $blade->view()->make('schema', ['tables' => $info])->render();
    $fh = fopen($fname, 'w');
    fwrite($fh, $lyx_schema);
    fclose($fh);
}

function getInfoFromDatabase()
{
    try {
        $db = new PDO('mysql:host=192.168.2.111;dbname=information_schema', DB_USER, DB_PASSWORD, [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);
        $stmt = $db->query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = \'' . SCHEMA . '\' AND TABLE_TYPE=\'BASE TABLE\';');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $db = new PDO('mysql:host=192.168.2.111;dbname=' . SCHEMA . ';charset=utf8', DB_USER, DB_PASSWORD, [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION]);

        foreach ($tables as $table) {
            $info[$table]['Create Table'] = $db->query("SHOW CREATE TABLE " . SCHEMA . "." . $table . ";")->fetch(PDO::FETCH_ASSOC)['Create Table'];
            $info[$table]['Columns'] = $db->query("SHOW FULL COLUMNS FROM  " . SCHEMA . "." . $table . ";")->fetchAll(PDO::FETCH_ASSOC);
        };
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    return $info;
}

function getRelationshipsFromSchema($tables, $db)
{
    for ($i = 0; $i < count($tables); $i++) {
        for ($j = $i; $j < count($tables); $j++) {
            if ($i != $j) {
                $table_left = $tables[$i];
                $table_right = $tables[$j];
                $table_left_description = $db->query("DESCRIBE " . SCHEMA . "." . $table_left . ";")->fetchAll(PDO::FETCH_ASSOC);
                $table_right_description = $db->query("DESCRIBE " . SCHEMA . "." . $table_right . ";")->fetchAll(PDO::FETCH_ASSOC);
                $relationship = null;
                foreach ($table_left_description as $tld) {
                    foreach ($table_right_description as $trd) {
                        if (!in_array($tld['Field'], ['DAT', 'AKTUELL', 'ID']) &&
                            !in_array($trd['Field'], ['DAT', 'AKTUELL', 'ID'])
                        ) {
                            if ($tld['Field'] == $trd['Field']) {
                                if (strpos($trd['Field'], '_ID') !== false)
                                    $relationship = $trd['Field'];
                            };
                        };
                    };
                };
                if (!is_null($relationship)) {
                    $relationships[] = [$table_left, $table_right, $relationship];
                };
            };
        };
    };
    return $relationships;
}

;

function getRelationshipsFromCode()
{

    $directory = new RecursiveDirectoryIterator(BERLUSSIMO_PATH);

    $iter = new RecursiveIteratorIterator($directory);

    foreach ($iter as $file) {
        if ($file->isFile() && $file->getExtension() == 'php') {
            print_r($file->getFilename() . "\n");
            $lines = file($file->getPathname());
            for ($k = 0; $k + 2 < count($lines); $k++) {
                if (preg_match("#^\s*(//|/\*|\*)#misU", $lines[$k], $matches2) || !preg_match("#SELECT#misU", $lines[$k])) {
                    continue;
                }
                $linesToInspect = $lines[$k] . $lines[$k + 1] . $lines[$k + 2];
                $pattern = getRelationshipPattern();
                if (preg_match($pattern, $linesToInspect, $matches)) {
                    if (strcmp(strtoupper($matches[1]), strtoupper($matches[3])) < 0) {
                        $table1 = strtoupper($matches[1]);
                        $table2 = strtoupper($matches[3]);
                    } else {
                        $table2 = strtoupper($matches[1]);
                        $table1 = strtoupper($matches[3]);
                    }
                    $relationships[] = [$table1, $table2, strtoupper($matches[4])];
                }
            }
        }
    }

    return array_unique($relationships, SORT_REGULAR);
}

function findUsages(&$infos)
{

    $directory = new RecursiveDirectoryIterator(BERLUSSIMO_PATH);

    $iter = new RecursiveIteratorIterator($directory);

    foreach ($iter as $file) {
        if ($file->isFile() && $file->getExtension() == 'php') {
            print_r($file->getFilename() . "\n");
            $lines = file($file->getPathname());
            for ($k = 0; $k + 2 < count($lines); $k++) {
                if (preg_match("#^\s*(//|/\*|\*)#misU", $lines[$k], $matches2) || !preg_match("#SELECT|INSERT|UPDATE|DELETE#misU", $lines[$k])) {
                    continue;
                }
                $linesToInspect = $lines[$k] . $lines[$k + 1] . $lines[$k + 2];
                foreach ($infos as $name => &$info) {
                    $pattern = getUsagePattern($name);
                    if (preg_match($pattern, $linesToInspect, $matches)) {
                        $info['Usages'][] = ['Lines' => $linesToInspect, 'File' => $file->getFilename()];
                    }
                }
            }
        }
    }

    return $infos;
}

function getRelationshipPattern()
{
    $pattern = "/([[:alnum:]_]+)\.([[:alnum:]_]+)['\)\s]*=[\s']*([[:alnum:]_]+)\.([[:alnum:]_]+)/misu";
    return $pattern;
}

function getUsagePattern($tname)
{
    $pattern = "/$tname/misu";
    return $pattern;
}


function generatePattern($left, $right)
{
    $pattern = "/SELECT.*((\s|\()$left.*$right(\s*|\))|(\s*|\()$right.*$left(\s*|\)))/isu";
    return $pattern;
}
