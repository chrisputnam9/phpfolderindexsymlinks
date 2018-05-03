# PHPFolderIndexSymlinks
Script to create an index folder based on the starting word character of each subfolder or file in
the source.  Each item will be indexed as a symlink.

## For Example
Suppose you have a directory:

Documents
 - word.txt
 - Abc.pdf
 - .test

You run command:

    fis Documents "Documents Index"

You now have a new directory:

Documents Index
 - a
    - Abc.pdf (symlink to original)
 - t
    - .test (symlink to original)
 - w
    - word.txt (symlink to original)

## Requirements 
 - Unix (tested on Linux, expected to work elsewhere)
 - PHP (tested with PHP 7.1, expected to work on 7.X and 5.X)

## Install 
To install for general common use (requires root/sudo privilege):

    curl -sL https://git.io/fis.php > fis && chmod +x fis && sudo mv fis /usr/bin/

Run the same command to update as needed.



## Usage

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

NOTE: This assumes you install as listed above, or otherwise alias the script to "fis"
