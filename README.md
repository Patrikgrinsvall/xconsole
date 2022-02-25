# SilentRidge App Starter

Note! Dont take this to seruous, its WIP!

Provides extensions to laravel, especially console app, but also some presets
for frontend.

There are plenty of commands in order to get started quickly. For example; when
running the ´starter´ command, also the shortcut tool for artisan, "z"
is added to laravel base folder. On linux its z.sh and on windows its z.bat and
it can be used in place of all artisan commands. So, there are at least two ways
to run each command and therefore some are listed twice below.

# Command list

## 1. Installation and helpers

```
+-----------------------------------------------------------------
|       command                         description
+-----------------------------------------------------------------
|- php artisan starter:help             Run installation and show this help
|- z.sh starter:help                    same as above   
+-----------------------------------------------------------------
```

All of the above makes initial installation and migrates, creates and seeds
database. It will error if .env is not setup correct.

## 2. Frontend scripts

```
+-----------------------------------------------------------------
|       command                         description
+-----------------------------------------------------------------
|- composer run install             Run installation of ALL frontend deps.
|- npm run scraper:build            build the scraper frontend
|- npm run scraper:prod             compile scraper frontend production
|- npm run sock:build               build the update server frontend
|- npm run sock:prod               build the update server for production      
|- npm run build-tryer              build nova component
+-----------------------------------------------------------------                              
```

All of the above commands are related to building the frontend.

## 3. Run time helpers

```
+-----------------------------------------------------------------
|       command                         description
+-----------------------------------------------------------------
|- php artisan z:z                  Super cache cleaner, views, configs, routes etc.
|                                   and also restart server and queue etc.
|- z.sh z:z                         same as above
|- composer run legacy              Runs local webserver with old scraper
+-----------------------------------------------------------------
                              
```

