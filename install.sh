#!/bin/bash

# Clone staging database and run config import:
# ./install.sh
#
# Do a clean site install using config_installer profile:
# ./install.sh clean

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd "$SCRIPT_DIR/web"

if [ -z "$1" ]
then
    cd "$SCRIPT_DIR/robo"
    ./robo site:install
elif [ "$1" == "clean" ]
then
    # Site:install command requires write permission on settings.php file
    chmod u+w sites/default/ sites/default/settings.php
    cd "$SCRIPT_DIR/web"
    ../vendor/bin/drush site:install config_installer -y --notify\
                                                      --account-name="drupal@eaudeweb.ro"\
                                                      --account-mail="drupal@eaudeweb.ro"\
                                                      --site-mail="drupal@eaudeweb.ro"\
                                                      --account-pass="password"
    # Site:install removes write permission after it's finished
    chmod u+w sites/default/ sites/default/settings.php
fi

