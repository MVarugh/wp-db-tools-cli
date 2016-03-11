# WP DB tools for WP-CLI

Provides commands to manipulate database tables.

Work in progress.

## Commands

```
$ wp db-table copy <TABLE> <NEW_TABLE> [--content]
```

## Upcoming commands

```
$ wp db-table delete <TABLE> [--no_confirm]
```


```
$ wp db-table create < table_schema.json
```

## Installation

Clone the repository and run composer:

```
$ git clone git@github.com:inpsyde/wp-db-tools-cli.git && cd wp-db-tools-cli
$ composer install --prefer-dist --optimize-autoloader
```