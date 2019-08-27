Imdb actors parser
------------------

Reqiremenets
------------
    php v7.1.3 or higher
    mysql

Usage:
-----
deploy
------
`git clone`

`composer update`

create database

load dump with 1000 actors to MySQL

`mysql -uusername -p db_name < 1000actors_dump.sql`

or if you want empty db dump - run

`mysql -uusername -p db_name < empty_db_dump.sql`

setup db connection:

uncomment row DATABASE_URL in 
/.env

run
------
`php bin/console parse:actors 1200 42`

1200 - how much actors want to parse

42 - from what actor id (starMeter) want to start parsing

to get help

`php bin/console parse:actors --help`  



Project description
-------------------
actor-parser - is a cli application.
For parsing html I'm using XPath, framework - symfony.


main app in 

`src/Console/Command/ParseActorsCommand.php`

parser service in 

`src/Service/ActorParser.php`

entities in 

`src/Entity`
