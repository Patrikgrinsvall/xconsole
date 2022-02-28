# XConsole


## PHP dev server 
Contains:
- Shortcuts to artisan, symfony and other php/node-js based console commands
- File watcher 
- Process runner/manager with support for multiple processes and their output
- Laravel local dev server
- Some slight color output variations
- Helpers for setting up easier command line debug tools
- Logger helper for better console and phpstorm log watcher.

## Install
- Only `composer require patrikgrinsvall/xconsole` should be needed, anything else is a bug
- Show built in commands with `./x x:help`, `x.bat x:help`, `x.sh x:help`, `php artisan x:help`, `php x:help`   

## Usage
- Create Installation, migration, and other customizable setup helpers with `x:install`
- Cusomize dev server by extending Commands/SrvCommand (to be improved with recepies)
