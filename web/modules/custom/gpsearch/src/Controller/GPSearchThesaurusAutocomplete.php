<?php

namespace Drupal\gpsearch\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpFoundation\Request;

/**
 * GP Search autocomplete class.
 */
class GPSearchThesaurusAutocomplete extends ControllerBase {

  /**
   * Returns a JSON data.
   *
   * @return JSON.
   */
  public function getTermsList(Request $request, $search_text) {
    if (!$search_text) {
      return '';
    }
    $current_language_code = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $db_connection = \Drupal::database();
    $query = $db_connection->select('taxonomy_term_field_data', 't');
    $query->fields('t', array('tid', 'name'));
    $query->join('taxonomy_term__field_synonyms', 'synonyms', 'synonyms.entity_id = t.tid');
    $query->condition('t.langcode', $current_language_code);
    $or_condition =  $query->orConditionGroup()
      ->condition('t.name', "%" . $db_connection->escapeLike($search_text) . "%", 'LIKE')
      ->condition('synonyms.field_synonyms_value', "%" . $db_connection->escapeLike($search_text) . "%", 'LIKE');
    $query->condition($or_condition);
    $query->distinct();
    $query->range(0, 10);

    $rows = $query->execute()->fetchAll();
    $results = [];
    if ($rows) {
      foreach ($rows as $row) {
        $url = \Drupal\Core\Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $row->tid])->toString();
        $results[] = [
          'name' => $row->name,
          'url' => $url,
        ];
      }
    }

    return new JsonResponse($results);
  }

}
