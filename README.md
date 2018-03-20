# PHPFolderIndexSymlinks

## Install 
You can run the script directly as needed.  To install for common use:

    curl -sL https://git.io/fis.php > fis && chmod +x fis && sudo mv fis /usr/bin/

Run the same command to update as needed.

## Usage
NOTE: This section assumes you install as listed above, or otherwise alias the script to "fis"

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
