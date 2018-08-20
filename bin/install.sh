#!/usr/bin/env bash

# Get the full path to the directory containing this script.
SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd "$SCRIPT_DIR/../web"

../vendor/bin/drush site-install config_installer -y

../vendor/bin/drush  user-password admin password

# @todo