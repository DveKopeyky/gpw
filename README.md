# Global Pact Website

## Installation
* Clone this repository
* Copy `web/sites/example.settings.local.php` to `web/sites/default/settings.local.php` and customize database credentials
* Copy `robo/example.robo.yml` to `robo/robo.yml` and customize the username and password to ones provided by system administrator
* Run `./install.sh`

## Updating Drupal Core
`composer update drupal/core webflo/drupal-core-require-dev symfony/* --with-dependencies`
