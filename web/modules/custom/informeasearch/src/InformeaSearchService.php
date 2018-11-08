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

use Drupal\Core\Plugin\PluginBase;
use Drupal\facets\Entity\Facet;
use Drupal\facets\Result\Result;

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

  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
    $this->server = Server::load('informea');
    $this->solrClient = $this->server->getBackend()->getSolrConnector();
    $this->query = $this->solrClient->getSelectQuery();
    $this->prepareRequest();
  }

  public function prepareRequest() {
    $index_keys = $this->getIndexKeys();

    $requests = [];
    if ($request_keys = $this->requestStack->getCurrentRequest()->query->get('f')) {
      foreach ($request_keys as $key) {
        list($k, $v) = explode(':', $key);
        if (array_key_exists($k, $index_keys)) {
          $requests[$index_keys[$k]][] = $v;
        }
      }
    }
    $this->alterQuery($this->buildQueryParams($requests));

    if ($search_api_fulltext = $this->requestStack->getCurrentRequest()->query->get('text')) {
      $this->query->setQuery($search_api_fulltext);
    }
  }

  public function search() {
  /* // Facets.
    $facetSet = $this->query->getFacetSet();
    $facetSet->setSort('count');
    $facetSet->setLimit(10);
    $facetSet->setMinCount(1);
    $facetSet->setMissing(FALSE);

    foreach ($this->getIndexKeys() as $key => $solr_key) {
      $facetSet->createFacetField($key)->setField($solr_key);
    }*/

    //$this->renderFacet();

    /** @var Solarium\QueryType\Select\Query\Query $query */

    $response = $this->solrClient->search($this->query);

    $r = json_decode($response->getBody());
    dpm($response->getBody());
  }


  public function facets() {

    // Facets.
    $facetSet = $this->query->getFacetSet();
    /** @var \Solarium\Component\FacetSet $facetSet */
    $facetSet->setSort('count');
    $facetSet->setLimit(10);
    $facetSet->setMinCount(1);
    $facetSet->setMissing(FALSE);

//    dpm($facetSet->getQueryInstance());



    foreach ($this->getIndexKeys() as $key => $solr_key) {
      $facetSet->createFacetField($key)->setField($solr_key);
    }

    //$this->renderFacet();

    /** @var Solarium\QueryType\Select\Query\Query $query */

    //dpm($this->query);

    $response = $this->solrClient->search($this->query);

    $r = json_decode($response->getBody());
    //dpm($response->getBody());
    return ($r->facet_counts->facet_fields);
  }

  public function buildQueryParams($params = []) {
    $query_params = [];
    foreach ($params as $k => $param) {
      $v = trim(implode(' OR ', $param));
      /*$query_params[] = [
        'key' => $k,
        'query' => "{$k}:({$v})",
      ];*/


      $query_params[] = [
        'key' => "facet:$k",
        'tag' => "facet:$k",
        'query' => "{$k}:({$v})",
      ];


    }
    dpm($query_params);
    return $query_params;
  }


  public function alterQuery($params = []) {
    $this->query->setFields(['*', 'score']);
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

  public function renderFacet()
  {

    /** @var \Drupal\facets\FacetInterface $facet */
    /*$facet = Facet::create([
      'id' => 'test_facet',
      'name' => 'Test facet',
    ]);*/

    $facet = Facet::load('test_facet');


    //$facet->setFacetSourceId($facet_source);
    //$facet->setFieldIdentifier($field);
    //$facet->setUrlAlias($id);

    $result_lower = new Result($facet, 5, '5', 1);
    $result_higher = new Result($facet, 150, '150', 1);
    $facet->setResults([$result_lower, $result_higher]);
    $facet->setWidget('links', ['show_numbers' => TRUE]);
    $facet->addProcessor([
      'processor_id' => 'url_processor_handler',
      'weights' => ['pre_query' => -10, 'build' => -10],
      'settings' => [],
    ]);
    $facet->setEmptyBehavior(['behavior' => 'none']);
    $facet->setOnlyVisibleWhenFacetSourceIsVisible(TRUE);

    $widget = $facet->getWidgetInstance();
    $build = $widget->build($facet);

    //$facet->save();

  // Create our facet.

    //$block_id = 'facet_block' . PluginBase::DERIVATIVE_SEPARATOR . $facet->id();




  }

}
