# XConsole

PHP Development helper with:
- Local development server
- File watcher 
- Process runner/manager with support for multiple processes and their output (through symfony process) 
- Laravel local dev server 
- Some slight color output variations (wip)
- Helpers for setting up easier command line debug tools
- Logger helper for better console and phpstorm log watcher. (wip)

## Install
- Only `composer require patrikgrinsvall/xconsole` should be needed, anything else is a bug
   

## Usage
- Show built in commands with `./x x:help`, `x.bat x:help`, `x.sh x:help`, `php artisan x:help`, `php x:help`
- Meaning, the `x` is a helper that can be runned in several ways
- Create Installation, migration, and other customizable setup helpers with `x:install`
- Cusomize dev server by extending Commands/SrvCommand (to be improved with recepies)
