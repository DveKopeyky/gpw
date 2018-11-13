<?php

namespace Drupal\informeasearch;
use Drupal\facets\Entity\Facet;
use Drupal\facets\Result\Result;
use Drupal\Core\Url;

/**
 * Class InformeaSearchFacetsService.
 */
class InformeaSearchFacetsService {

  /**
   * Drupal\informeasearch\InformeaSearchService definition.
   *
   * @var \Drupal\informeasearch\InformeaSearchService
   */
  protected $informeasearch;
  /**
   * Constructs a new InformeaSearchFacetsService object.
   */
  public function __construct(InformeaSearchService $informeasearch) {
    $this->informeasearch = $informeasearch;
  }
  public function facetBuild( $facet_id = '', $field = NULL) {
    if (!Facet::load($facet_id)) {
      Facet::create([
        'id' => $facet_id,
        'name' => $facet_id,
      ])->save();
    }
    $facet = Facet::load($facet_id);
    $facet->setUrlAlias($field);


    $facet_items = $this->facetItems($field);
    $facet_labels = $this->facetLabels($field, $facet_items);

    $facet_results = [];

    if ($facet_items) {
      foreach ($facet_items as $facet_key => $facet_count) {
        $label = isset($facet_labels[$facet_key]) ? $facet_labels[$facet_key] : $facet_key;
        $res = new Result($facet, $facet_key, $label, $facet_count);
        $url = $this->facetUrl($field, $facet_key);
        $res->setUrl($url['url']);
        $res->setActiveState($url['active']);
        $facet_results[] = $res;
      }
    }
    if ($facet_results) {
      $facet->setResults($facet_results);
      //$facet->setWidget('links', ['show_numbers' => TRUE]);
      $facet->setWidget('enhanced_checkboxes',
        [
          'show_numbers' => TRUE,
          'collapsed' => TRUE,
          'subtitle' => $this->facetSubtitle($field),
          'default_option_label' => 'Choose',
        ]);
      $widget = $facet->getWidgetInstance();
      $build = $widget->build($facet);
    }
    $build['#cache']['max-age'] = 0;
    return $build;
  }


  public function facetItems($field) {
    $search_results = $this->informeasearch->search();
    $facets = $search_results->facet_counts->facet_fields;
    $res = [];
    if ($facets->{$field}) {
      $f = $facets->{$field};
      for( $i=0; $i <=count($f); $i += 2 ) {
        if (isset($f[$i+1])) {
          $res[$f[$i]] = $f[$i+1];
        }
      }
    }
    return $res;
  }

  public function facetUrl($field, $facet_key){
    $params = [];
    $active = FALSE;
    if ($request_keys = $this->informeasearch->getRequestStack()->getCurrentRequest()->query->get('f')) {
      foreach ($request_keys as $key) {
        list($k, $v) = explode(':', $key);
        if ($k == $field && $v == $facet_key) {
          $active = TRUE;
        } else {
          if (array_key_exists($k, $this->informeasearch->getIndexKeys())){
            $params["f"][] = "$k:$v";
          }
        }
      }
    }
    if (!$active ) {
      $params["f"][] = "$field:$facet_key";
    }
    return [
      'url' => new Url('informeasearch.informea_search_controller_search', $params),
      'active' => $active,
    ];
  }

  public function facetSubtitle($field){
    switch ($field) {
      case 'type':
        return t('Filter by type of content');
        break;
      case 'field_mea_topic':
        return t('Filter by topic');
        break;
      case 'field_region':
        return t('Filter by region');
        break;
      case 'field_informea_tags':
        return t('Filter by glossary term(s)');
        break;
      default:
        return NULL;
        break;
    }
  }

  public function facetLabels($field = NULL, $facet_items = []) {
    switch($field) {
      case 'type':
        return [
          'treaty' => t('Treaty'),
          'decision' => t('Decision'),
          'legislation' => t('Legislation'),
          'document' => t('Document'),
          'goal' => t('Goal'),
          'declaration' => t('Declaration'),
          'literature' => t('Literature'),
        ];
        break;
      case 'field_mea_topic':
        return [
          850 => t('Biodiversity'),
          851 => t('Chemicals and Waste'),
          852 => t('Climate Change and Atmosphere'),
          853 => t('Drylands'),
          6546 => t('Environmental Governance'),
          854 => t('Financing &amp; Trade'),
          855 => t('International Cooperation'),
          6545 => t('Land and Agriculture '),
          4417 => t('Marine and Freshwater'),
          856 => t('Species'),
          857 => t('Wetlands &amp; National heritage sites'),
        ];
        break;
      case 'field_informea_tags':
        if ($ids = array_keys($facet_items)) {
          # $connection = \Drupal::database();
          # $query = $connection->query("SELECT fi.field_informea_tid_value, t.name FROM taxonomy_term__field_informea_tid fi JOIN taxonomy_term_field_data t ON fi.entity_id = t.tid WHERE fi.field_informea_tid_value IN (" . implode(',' , $ids) . ")");
          # return $query->fetchAllKeyed();
          $query = \Drupal::database()->select('taxonomy_term__field_informea_tid', 'fi');
          $query->join('taxonomy_term_field_data', 't', 'fi.entity_id = t.tid');
          $query->fields('fi', ['field_informea_tid_value']);
          $query->fields('t', ['name']);
          $query->condition('fi.field_informea_tid_value', $ids, 'IN');
          return $query->execute()->fetchAllKeyed();
        }
        return [];
        break;
      case 'field_region':
        return [
          410 => t('Africa'),
          411 => t('Asia and the Pacific'),
          412 => t('Europe'),
          1118 => t('Global'),
          413 => t('Latin America and the Caribbean'),
          414 => t('North America'),
          415 => t('Polar: Arctic'),
          416 => t('West Asia'),
        ];
        break;
      default:
        break;
    }
  }

}
