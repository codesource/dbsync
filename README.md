# dbsync
Database synchronisation tool

## Description
This script allows you to synchronise database structure between two database. Tables, columns, indexes and constraints are compared and SQL are generated to update destination.

## Options
### General options
        --dryrun
                dry run queries (no modification will be commited to destination)
        -h | --help
                show this help
        -p | --print
                only print all required queries to update destination
        -q | --quiet
                quietly execute queries without processing informations
        -v | --verbose
                verbosely list queries and processing informations
        --file
                use configuration file to set other options

### Source options
        --database1
                source database name
        --host1
                source host server address
        --password1
                user password to connect to source host server
        --port1
                source host server port
        --username1
                username to connect to source host server

### Destination options
        --database2
                destination database name
        --host2
                destination host server address
        --password2
                user password to connect to destination host server
        --port2
                destination host server port
        --username2
                username to connect to destination host server

## Examples
        dbsync --host1=localhost --database1=db1 --username1=usr1 --password1=123456 --host2=localhost --database2=db2 --username2=usr2 --password2=123456 --verbose -dryrun
        dbsync --file=myconf.txt
