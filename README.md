# Global Pact Website

## Installation
* Clone this repository
* Copy `web/sites/example.settings.local.php` to `web/sites/default/settings.local.php` and customize database credentials
* Copy `example.robo.yml` to `robo.yml` and customize the username and password to ones provided by system administrator
* Get the database dump and import: `./vendor/bin/robo sql:sync`
* Get the files archive: `./vendor/bin/robo files:sync`
* Enable development: `./vendor/bin/robo site:develop`

## Updating Drupal Core
`composer update drupal/core webflo/drupal-core-require-dev symfony/* --with-dependencies`
