<?php

namespace Drupal\gpbase\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Serialization\Yaml;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class GpbaseCommands extends DrushCommands {

  /**
   * Import content from /content/sync directory.
   *
   * @param string|NULL $name
   *   If NULL, all entities found within content directory will be imported.
   *
   * @command gpbase:importContent
   *
   * @throws \Exception
   */
  public function importContent($name = NULL, $options = []) {
    global $content_directories;
    if (empty($content_directories['sync'])) {
      throw new \Exception(t('The "sync" content directory configuration is missing.'));
    }
    /** @var \Drupal\content_sync\Importer\ContentImporter $contentImporter */
    $contentImporter = \Drupal::service('content_sync.importer');

    if (empty($name) || strtolower($name) == 'null') {
      $directory = $content_directories['sync'];
      $files = array_diff(scandir($directory), ['..', '.']);
    }
    else {
      $files = ["$name.yml"];
    }

    foreach ($files as $file) {
      $data = Yaml::decode(file_get_contents($directory . '/' . $file));
      try {
        $entity = $contentImporter->importEntity($data);
        if (!empty($entity)) {
          $this->logger()->notice("Successfully imported $file");
        }
        else {
          $this->logger()->error("Could not import $file");
        }
      }
      catch (\Exception $e) {
        $this->logger()->error("Could not import $file");
      }
    }
  }

}
