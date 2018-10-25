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
    $CurrentLanguageCode = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $DBConnection = \Drupal::database();
    $Query = $DBConnection->select('taxonomy_term_field_data', 't');
    $Query->fields('t', array('tid', 'name'));
    $Query->condition('langcode', $CurrentLanguageCode);
    $Query->condition('name', "%" . $DBConnection->escapeLike($search_text) . "%", 'LIKE');
    $Query->range(0, 10);

    $Rows = $Query->execute()->fetchAll();
    $Results = [];
    if ($Rows) {
      foreach ($Rows as $Row) {
        $URL = \Drupal\Core\Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $Row->tid])->toString();
        $Results[] = [
          'name' => $Row->name,
          'url' => $URL,
        ];
      }
    }

    return new JsonResponse($Results);
  }

}
