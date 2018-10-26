<?php

namespace Drupal\gpbase\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Serialization\Yaml;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
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
class GpbaseInitialImportContent extends DrushCommands {

  const VOCABULARY_LEO = 'thesaurus';
  const VOCABULARY_TOPICS = 'topics';
  const VOCABULARY_RESOURCE_TYPES = 'resource_types';
  const VOCABULARY_DOCUMENT_TYPES = 'document_types';

  protected $dry_run = FALSE;

  /**
   * Import GPE initial content (created via Google Docs) from arbitrary directory.
   *
   * @param string $json_file
   *   Path to data.json with content metadata
   *
   * @command gpbase:importInitialContent
   *
   * @throws \Exception
   */
  public function importInitialContent($json_file = NULL, $options = ['dry-run' => false]) {
    $this->dry_run = !empty($options['dry-run']);
    if (empty($json_file)) {
      throw new \Exception('Missing path parameter');
    }
    $nodes = $this->parseData($json_file);
    foreach($nodes as $node) {
      if (!$this->dry_run) {
        $node->save();
      }
    }
  }


  /**
   * @param $json_file
   *
   * @return array
   * @throws \Exception
   */
  protected function parseData($json_file) {
    $ret = [];
    $storage = dirname(realpath($json_file));
    if ($json = json_decode(file_get_contents($json_file))) {
      foreach ($json as $row) {
        switch($row->content_type) {
          case 'document':
            $ret[] = $this->prepareDocument($row, $storage);
            break;
          case 'resource':
            $ret[] = $this->prepareResource($row);
            break;
          case 'video':
            $ret[] = $this->prepareVideo($row);
            break;
        }
      }
    }
    else {
      throw new \Exception("Cannot parse {$json_file}");
    }

    return $ret;
  }

  /**
   * Initialize a new Video.
   *
   * @param \stdClass $row
   *   JSON row.
   *
   * @return \Drupal\node\Entity\Node
   */
  protected function prepareVideo($row) {
    $terms = self::termsToFieldValues($row->tags);
    $array = [
      'type' => 'video',
      'title' => $row->title,
      'field_video_embed' => $row->link,
      'field_thesaurus' => $terms,
      'field_highlighted_course' => ['target_id' => $row->course],
    ];
    if ($topic = self::getTidByName($row->topic, self::VOCABULARY_TOPICS)) {
      $array['field_topic'] = ['target_id' => $topic];
    }
    return Node::create($array);
  }

  /**
   * Initialize a new External Resource.
   *
   * @param \stdClass $row
   *   JSON row.
   *
   * @return \Drupal\node\Entity\Node
   */
  protected function prepareResource($row) {
    $terms = self::termsToFieldValues($row->tags);
    $array = [
      'type' => 'external_resource',
      'title' => $row->title,
      'field_link' => $row->link,
      'field_thesaurus' => $terms,
      'field_highlighted_course' => ['target_id' => $row->course],
    ];
    if ($topic = self::getTidByName($row->topic, self::VOCABULARY_TOPICS)) {
      $array['field_topic'] = ['target_id' => $topic];
    }
    if ($type = self::getTidByName($row->document_type, self::VOCABULARY_RESOURCE_TYPES)) {
      $array['field_type'] = ['target_id' => $type];
    }
    return Node::create($array);
  }

  /**
   * Initialize a new Document.
   *
   * @param \stdClass $row
   *   JSON row.
   * @param string $storage
   *   File storage
   *
   * @return \Drupal\node\Entity\Node
   *
   * @throws \Exception
   */
  protected function prepareDocument($row, $storage) {
    $array = [
      'type' => 'document',
      'title' => $row->title,
      'field_highlighted_course' => ['target_id' => $row->course],
    ];

    $entities = [];
    foreach ($row->tags as $tag) {
      if ($tid = self::getTidByName($tag->term)) {
        $et = \Drupal::getContainer()->get('entity_type.manager');
        $arr = [
          'type' => 'tag',
          'field_tags' => ['target_id' => $tid]
        ];
        if (!empty($tag->page)) {
          $arr['field_page_number'] = $tag->page;
        }
        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        $entity = $et->getStorage('child_entity')->create($arr);
        if (!$this->dry_run) {
          $entity->save();
          $entities[] = ['target_id' => $entity->id()];
        }
      }
    }
    if (!empty($entities)) {
      $array['field_tags'] = $entities;
    }

    if ($topic = self::getTidByName($row->topic, self::VOCABULARY_TOPICS)) {
      $array['field_topic'] = ['target_id' => $topic];
    }
    if ($type = self::getTidByName($row->document_type, self::VOCABULARY_DOCUMENT_TYPES)) {
      $array['field_type'] = ['target_id' => $type];
    }

    if (!empty($row->link)) {
      $file = $storage . '/' . $row->link;
      $content = file_get_contents($file);
      $filename = basename($file);
      if (!$this->dry_run) {
        if ($f = file_save_data($content, 'public://documents/' . $filename, FILE_EXISTS_REPLACE)) {
          $array['field_file'] = ['target_id' => $f->id()];
        }
      }
    }

    return Node::create($array);
  }

  static function termsToFieldValues($tags) {
    $ret = [];
    foreach($tags as $tag) {
      if ($tid = self::getTidByName($tag->term)) {
        $ret[] = ['target_id' => $tid];
      }
      else {
        \Drupal::logger('')->error("Cannot find term {$tag->term}");
      }
    }
    return $ret;
  }

  /**
   * Retrieve a Thesaurus term by name.
   *
   * @param string $name
   *   Term name.
   *
   * @param string $vocabulary
   *   Which vocabulary.
   *
   * @return integer|null
   *
   */
  static function getTidByName($name, $vocabulary = self::VOCABULARY_LEO) {
    $q = \Drupal::entityQuery('taxonomy_term');
    $q->condition('vid', $vocabulary);
    $q->condition('name', $name);
    $q->range(0, 1);
    if ($r = $q->execute()) {
      return reset($r);
    }
    return null;
  }
}
