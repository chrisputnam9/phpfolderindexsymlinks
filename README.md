# phpfolderindexsymlinks

## Install 
You can run the script directly as needed.  To install for common use:

    curl -s https://raw.githubusercontent.com/chrisputnam9/phpfolderindexsymlinks/master/fis.php > fis && chmod +x fis && mv fis /usr/bin/

Run the same command to update as needed.

## Usage
NOTE: This section assumes you install or alias to "fis" - you can replace all occurrances of "fis" with
php fis.php in the information below if you prefer to run the script without full installation.

    fis <source> [target]

source (required) - The folder to index
target (optional) - The folder in which to create the index.  If not specified, it will
default to "source__index"
