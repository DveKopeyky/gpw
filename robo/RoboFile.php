<?php

// @codingStandardsIgnoreFile

use Robo\Robo;
use Robo\Tasks;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends Tasks {

  protected $drush = '../vendor/bin/drush';

  /**
   * Sync DB from staging server.
   *
   * @return null|\Robo\Result
   * @throws \Robo\Exception\TaskException
   *
   * @command sql:sync
   */
  public function sqlSync() {
    $config = Robo::config();
    $url =  $config->get('project.sync.sql.url');
    $username = $config->get('project.sync.sql.username');
    $password = $config->get('project.sync.sql.password');
    $sql_dump = '../sql/db.sql';
    $sql_dump_gz = '../sql/db.sql.gz';

    $execStack = $this->taskExecStack()->stopOnFail();
    $execStack->exec("rm -f $sql_dump $sql_dump_gz");
    $execStack->exec("curl $url --create-dirs -o $sql_dump_gz -u $username:$password");
    $execStack->exec("gzip -d $sql_dump_gz");
    $execStack->exec("{$this->drush} sql:drop -y");
    $execStack->exec("{$this->drush} sql:query --file=$sql_dump -y");
    return $execStack->run();
  }

  /**
   * Export database dump to a file.
   *
   * @param string $destination
   *  Destination file path.
   * @param bool $gzip
   *  If TRUE, the dump will be gzipped.
   *
   * @return \Robo\Result
   *
   * @command sql:dump
   */
  public function sqlDump($destination = 'sites/default/files/sync/database.sql', $gzip = TRUE) {
    $task = $this->taskExec("{$this->drush} sql:dump --result-file={$destination}");
    if ($gzip === TRUE) {
      $task->arg('--gzip');
    }
    return $task->run();
  }

  /**
   * Sync public files from staging server.
   *
   * @return null|\Robo\Result
   * @throws \Robo\Exception\TaskException
   *
   * @command files:rsync
   */
  public function filesRsync() {
    $config = Robo::config();
    $url =  $config->get('project.sync.sql.files_url');
    $username = $config->get('project.sync.sql.username');
    $password = $config->get('project.sync.sql.password');
    $files_tar_gz = 'files.tar.gz';

    $execStack = $this->taskExecStack()->stopOnFail()->dir('../web/sites/default');
    $execStack->exec("rm -f $files_tar_gz");
    $execStack->exec("curl $url -o $files_tar_gz -u $username:$password");
    $execStack->exec("rm -rf files/*");
    $execStack->exec("tar -xzvf $files_tar_gz");
    $execStack->exec("rm -f $files_tar_gz");
    return $execStack->run();
  }

  /**
   * Archive files into sites/default/files/sync/files.tar.gz
   *
   * @return null|\Robo\Result
   * @throws \Robo\Exception\TaskException
   *
   * @command files:archive
   */
  public function filesArchive() {
    $execStack = $this->taskExecStack()->stopOnFail()->dir('../web/sites/default/files/sync/');
    $execStack->exec("rm -f files.tar.gz");
    $execStack->exec("tar cvfz files.tar.gz --exclude=files/sync ../../files");
    return $execStack->run();
  }

  /**
   * Install the local instance.
   *
   * @return bool|null|\Robo\Result
   * @throws \Robo\Exception\TaskException
   *
   * @command site:install
   */
  public function siteInstall() {
    if ($this->sqlSync()->wasSuccessful()) {
      $execStack = $this->taskExecStack()->stopOnFail();
      $execStack->exec("{$this->drush} user:password drupal@eaudeweb.ro password");
      $execStack->exec("{$this->drush} updatedb -y");
      $execStack->exec("{$this->drush} entup -y");
      $execStack->exec("{$this->drush} cim sync -y");
      $execStack->exec("{$this->drush} en config devel webprofiler -y");
      $execStack->exec("{$this->drush} cim dev --partial -y");
      $execStack->exec("{$this->drush} cr");
      return $execStack->run();
    }
    return FALSE;
  }
}