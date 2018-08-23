#!/bin/bash

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd "$SCRIPT_DIR/web"

../vendor/bin/drush site:install config_installer -y
../vendor/bin/drush user:password admin password

# Site:install command removes write permissions on settings.php file
chmod u+w sites/default/settings.php

