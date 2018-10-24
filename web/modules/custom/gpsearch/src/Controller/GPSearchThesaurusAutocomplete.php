<?php

namespace Drupal\gpsearch\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    $CurrentLanguageCode = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $DBConnection = \Drupal::database();
    $Query = $DBConnection->select('taxonomy_term_field_data', 't');
    $Query->fields('t', array('tid', 'name'));
    $Query->condition('langcode', $CurrentLanguageCode);
    $Query->condition('name', "%" . $DBConnection->escapeLike($search_text) . "%", 'LIKE');
    $Query->range(0, 10);

    $Results = $Query->execute()->fetchAll();

    return new JsonResponse($Results);
  }

}
