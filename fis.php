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
        if (preg_match('/^[^a-z0-9]*([a-z0-9])/i', $filename, $matches))
        {
            $index = strtolower($matches[1]);

            // reduce all numbers to single folder
            if (is_numeric($index))
                $index='0-9';
        }

        lg(" --- Indexing under '$index'");

        $to_dir = $target . DS . $index;

        if (!in_array($index, $index_cache))
        {
            lg(" --- Creating folder for '$index'");
            if (empty($config['dryrun']))
                mkdir($to_dir, 0755, true);
            $index_cache[]= $index;
        }

        $to_dir = realpath($to_dir);
        $to = $to_dir . DS . $filename; 
        $from = $source . DS . $filename;

        lg(" --- Symlinking '$from' to '$to'");
        $from = getRelativePath($to_dir, $from);
        lg(" --- Relative path: '$from'");
        if (empty($config['dryrun']))
        {
            $original_dir = getcwd();

            $success = chdir($to_dir);
            if (empty($success))
                lg("Unable to change directory to '$to_dir'", 4);

            $success = symlink($from, $filename);
            if (empty($success))
                lg("Unable to symlink ($from, './')", 5);

            $success = chdir($original_dir);
            if (empty($success))
                lg("Unable to change directory back to '$original_dir'", 6);
        }

    }
    closedir($dir);

    lg('Success!');

} catch (Exception $e) {
    lg($e->getMessage(), 1);
}

/**
 * Get relative path from given path to another
 * Credit: https://goo.gl/g53sMe
 */
function getRelativePath($from, $to)
{
    // remove trailing slashes
    $from = rtrim($from, DS);
    $to   = rtrim($to, DS);

    // Add trailing slash if directory
    $from = is_dir($from) ? $from . DS : $from;
    $to = is_dir($to) ? $to . DS : $to;

    // split by directory separators
    $from     = explode(DS, $from);
    $to       = explode(DS, $to);
    $relPath  = $to;

    // Loop through from levels
    foreach($from as $depth => $dir) {

        // find first non-matching dir
        if($dir === $to[$depth]) {
            // ignore this directory
            array_shift($relPath);
        } else {
            // get number of remaining dirs to $from
            $remaining = count($from) - $depth;
            if($remaining > 1) {
                // add traversals up to first matching dir
                $padLength = (count($relPath) + $remaining - 1) * -1;
                $relPath = array_pad($relPath, $padLength, '..');
                break;
            } else {
                $relPath[0] = './' . $relPath[0];
            }
        }
    }

    return implode('/', $relPath);
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
        if (in_array($error, array(2,3)))
        {
            echo "--------------------\n";
            usage();
        }
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
