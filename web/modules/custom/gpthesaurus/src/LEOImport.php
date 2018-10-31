<?php

/**
 * @file
 * Contains the import code to get LEO from the InforMEA API at https://www.informea.org/ws/leo
 */


namespace Drupal\gpthesaurus;


use Drupal\taxonomy\Entity\Term;

class LEOImport {

  const VOCABULARY_LEO = 'thesaurus';
  const VOCABULARY_TOPICS = 'topics';

  protected $terms = [];


  function __construct() {}


  /**
   * @param string $sourceUrl
   *   URL end-point load the JSON from.
   *
   * @return array|mixed
   *   Array of serialized term objects without any key.
   *
   * @throws \Exception When data cannot be found / parsed
   */
  function load($sourceUrl) {
    if ($json = file_get_contents($sourceUrl)) {
      if ($rows = json_decode($json, false)) {
        $ret = $rows;
      }
      else {
        throw new \Exception('Failed to parse JSON data from remote endpoint: ' . $sourceUrl);
      }
    }
    else {
      throw new \Exception('Failed to load data from remote endpoint: ' . $sourceUrl);
    }
    return $ret;
  }

  /**
   * @param array $terms
   * @param bool $dryRun
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  function import($terms, $dryRun = false) {
    foreach ($terms as $row) {
      $key = @trim($row->uri);
      if (!empty($key)) {
        $term = self::getTermByUri($key);
        if (empty($term)) {
          $term = Term::create([
            'vid' => self::VOCABULARY_LEO,
          ]);
        }
        $this->prepare($row, $term);
        if (!$dryRun) {
          $term->save();
        }
      }
    }
    // Set hierarchy and related terms in second pass (after all are created).
    foreach ($terms as $row) {
      $key = @trim($row->uri);
      if ($term = self::getTermByUri($key)) {
        $related = [];
        foreach ($row->related as $uri) {
          if ($relId = self::getTermByUri($uri)) {
            $related[] = $relId;
          }
        }
        $term->set('field_related_leo_terms', $related);

        $parent_key = @trim($row->parent_uri);
        $parent = ['target_id' => 0];
        if ($parentNode = self::getTermByUri($parent_key)) {
          $parent = ['target_id' => $parentNode->id()];
        }
        $term->set('parent', $parent);
      }
      $term->save();
    }
  }


  /**
   * Populate term with data from API.
   *
   *   Term URI.
   * @param \stdClass $row
   *   API term object
   * @param \Drupal\taxonomy\TermInterface $term
   *   Drupal term
   */
  function prepare($row, $term) {
    // Assign fields
    $field_mapping = [
      'informea_page' => 'field_informea_url',
      'name' => 'name',
      'uri' => 'field_semantic_uri',
      'definition' => 'field_definitions',
      'synonyms' => 'field_synonyms',
      'topics' => 'field_topics',
      'id' => 'field_informea_tid',
    ];
    foreach ($field_mapping as $api => $drupal) {
      switch ($api) {
        case 'name':
          $term->setName(mb_convert_encoding($row->$api, "UTF-8", "HTML-ENTITIES"));
          break;
        case 'topics':
          $topics = [];
          foreach($row->topics as $topicName) {
            // "Fix" ... you know
            if (strtolower($topicName) == 'biodiversity') {
              $topicName = 'Biological Diversity';
            }
            else if (strtolower($topicName) == 'climate change and atmosphere') {
              $topicName = 'Climate and Atmosphere';
            }
            if ($topicId = self::getTopicByName($topicName)) {
              $topics[] = $topicId;
            }
          }
          $term->set($drupal, $topics);
          break;
        case 'synonyms':
        case 'definition':
          foreach($row->$api as &$value) {
            $value = mb_convert_encoding($value, "UTF-8", "HTML-ENTITIES");
          }
          $term->set($drupal, $row->$api);
          break;
        default:
          $term->set($drupal, $row->$api);
      }
    }
  }

  /**
   * Retrieve a term by its semantic URI.
   *
   * @param string $uri
   *   Pass the URI from semantic engine.
   *
   * @return \Drupal\taxonomy\TermInterface|null
   *
   */
  static function getTermByUri($uri) {
    $uri = str_replace('https://', 'http://', $uri);
    $uri_https = str_replace('http://', 'https://', $uri);
    $q = \Drupal::entityQuery('taxonomy_term');
    $q->condition('vid', self::VOCABULARY_LEO);
    $q->condition('field_semantic_uri', [$uri, $uri_https], 'IN');
    $q->range(0, 1);
    if ($r = $q->execute()) {
      if ($tid = reset($r)) {
        return Term::load($tid);
      }
    }
    return null;
  }

  /**
   * Retrieve a topic by its name.
   *
   * @param string $name
   *   Topic English name
   *
   * @return \Drupal\taxonomy\TermInterface|null
   *
   */
  static function getTopicByName($name) {
    $q = \Drupal::entityQuery('taxonomy_term');
    $q->condition('vid', self::VOCABULARY_TOPICS);
    $q->condition('name', $name);
    if ($r = $q->execute()) {
      if ($tid = reset($r)) {
        return Term::load($tid);
      }
    }
    return null;
  }

}
