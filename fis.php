#!/usr/bin/php
<?php

/**
 * README:
 * https://github.com/chrisputnam9/phpfolderindexsymlinks
 */

// Anything uncaught
ini_set('display_errors', 1);
ini_set('html_errors', 0);
error_reporting(E_ERROR);

define(DS, DIRECTORY_SEPARATOR);

try {

    // Options
    $config = array(
        'dryrun' => false,
        'help' => false,
        'ignore' => array('.', '..'),
        'verbose' => false,
    );

    lg('Evaluating Options');
    foreach ($argv as $a => $arg)
    {
        if (preg_match('/^--([^\=]+)(\=(.*))?$/', $arg, $matches))
        {
            $key = $matches[1];
            if (!isset($config[$key])) continue;

            $value = isset($matches[2]) ? $matches[3] : true;

            if (is_array($config[$key]))
                $config[$key][] = $value;
            else
                $config[$key] = $value;

            unset($argv[$a]);
        }
    }
    $argv = array_values($argv);

    lg('Processed Options:');
    lg($config);

    if ($config['help'])
    {
        usage();
        exit;
    }

    if ($config['dryrun']) $config['verbose'] = true;

    if (empty($argv[1]) or !is_dir($argv[1]))
        lg("Must specify valid source directory to be indexed", 2);

    $source = realpath($argv[1]);
    $target = empty($argv[2]) ? $source."__index" : $argv[2];

    if (empty($config['dryrun']))
    {
        mkdir($target, 0755, true);
        if (!is_dir($target)) lg("Failed to create target directory ($target)", 3);
    }

    lg("Indexing '$source' to '$target'");

    lg("Opening '$source' and looping over contents");
    $dir = opendir($source);
    $index_cache = array();
    while ($filename = readdir($dir))
    {
        if (in_array($filename, $config['ignore'])) continue;

        lg(" - $filename");

        $index = '_';
        if (preg_match('/^\W*(\w)/', $filename, $matches))
        {
            $index = strtolower($matches[1]);
        }

        lg(" --- Indexing under '$index'");

        if (!in_array($index, $index_cache))
        {
            lg(" --- Creating folder for '$index'");
            if (empty($config['dryrun']))
                mkdir($target . DS . $index, 0755, true);
            $index_cache[]= $index;
        }

        $from = $source . DS . $filename;
        $to = $target . DS . $index . DS . $filename; 
        lg(" --- Symlinking '$from' to '$to'");
        if (empty($config['dryrun']))
            symlink($from, $to);

    }
    closedir($dir);

    lg('Success!');

} catch (Exception $e) {
    lg($e->getMessage(), 1);
}

/**
 * Log function
 */
function lg($data, $error=false)
{
    global $config;

    if (empty($config['verbose']) and empty($error)) return;

    if (is_bool($data))
        $data = $data ? '(Boolean) TRUE' : '(Boolean) FALSE';
    if (is_array($data) or is_object($data))
        $data = print_r($data, true);

    if ($error)
        $data = "ERROR ($error): " . $data;

    if ($config['verbose'])
        $data = date('Y-m-d H:i:s ... ') . $data;

    echo $data . "\n";

    if ($error)
    {
        echo "--------------------\n";
        usage();
        exit($error);
    }
}

/**
 * Usage
 */
function usage()
{
    echo <<<USAGE
USAGE:
    fis {--option=value} <source> [target]

    source (required) - The folder to index
    target (optional) - The folder in which to create the index.  If not specified, it will
                            default to "source__index"

    Options:
        --dryrun      - show output without any actual side effects.  Automatically enables verbose.
        --help        - show usage help
        --ignore=path - ignore file by the given name within the source directory.  Repeat this
                        flag for multiple ignores.
        --verbose     - display timestamps and verbose output
USAGE;

}
