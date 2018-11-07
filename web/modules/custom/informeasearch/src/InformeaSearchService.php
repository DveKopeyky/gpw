<?php

namespace Drupal\informeasearch;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\search_api\Entity\Server;

use Drupal\search_api\Entity\Index;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;
use Solarium\Client;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Solarium\QueryType\Select\Query\Query;


/**
 * Class InformeaSearchService.
 */
class InformeaSearchService {

  /**
   * Drupal\Core\Entity\EntityManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;
  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;
  /**
   * Constructs a new InformeaSearchService object.
   */

  protected $index_id = 'default_index';
  /** @var \Drupal\search_api\Entity\Index */
  protected $index = NULL;
  protected $solrClient = NULL;
  protected $server = NULL;
  protected $server_config = array();
  protected $solr_field_mappings = array();
  protected $search_field_mappings = array();
  protected $query;

  public function __construct() {
    $this->server = Server::load('informea');
    $this->solrClient = $this->server->getBackend()->getSolrConnector();
    $this->query = $this->solrClient->getSelectQuery();
    $this->query->setFields(['*', 'score']);
  }

  public function search() {

    $facets = [
      'field_type_of_text' => [
        'title' => t('Type of court'),
        'placeholder' => t('Add types…'),
        'bundle' => 'document_types',
      ],
    ];






    // Facets.
    $facetSet = $this->query->getFacetSet();
    $facetSet->setSort('count');
    $facetSet->setLimit(10);
    $facetSet->setMinCount(1);
    $facetSet->setMissing(FALSE);

    foreach ($this->getIndexKeys() as $key => $solr_key) {
      $facetSet->createFacetField($key)->setField($solr_key);
    }

    /** @var Solarium\QueryType\Select\Query\Query $query */

    $response = $this->solrClient->search($this->query);

    $r = json_decode($response->getBody());
    //dpm($r);
    dpm($r->facet_counts->facet_fields);
  }

  public function buildQueryParams($params = []) {
    $query_params = [];
    foreach ($params as $k => $param) {
      $v = trim(implode(' OR ', $param));
      $query_params[] = [
        'key' => "facet:$k",
        'tag' => "facet:$k",
        'query' => "{$k}:({$v})",
      ];
      /*$query_params[] = [
        'key' => $k,
        'query' => "{$k}:({$v})",
      ];*/
    }
    return $query_params;
  }

  public function alterQuery($params = []) {
    foreach ($params as $param) {
      $fq = $this->query->createFilterQuery($param);
      $this->query->addFilterQuery($fq);
    }
  }

  public function getIndexKeys() {
    $keys =
      [
        'type' => 'ss_type',
        'field_mea_topic' => 'im_field_mea_topic',
        'field_region' => 'im_field_region',
      ];
   return $keys;
  }

  public function getQuery() {
    return $this->query;
  }

}
