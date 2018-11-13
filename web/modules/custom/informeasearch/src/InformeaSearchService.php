<?php

namespace Drupal\informeasearch;

use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\search_api\Entity\Server;

/**
 * Class InformeaSearchService.
 */
class InformeaSearchService {

  public static $results = NULL;

  protected $requestStack;

  /** @var \Drupal\search_api\Entity\Index */
  protected $solrClient = NULL;

  protected $server = NULL;

  protected $query;

  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  public function getRequestStack(){
    return $this->requestStack;
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

    if( !isset(static::$results) ){
      // Set the results;
      $this->server = Server::load('informea');
      $this->solrClient = $this->server->getBackend()->getSolrConnector();
      $this->query = $this->solrClient->getSelectQuery();
      $this->prepareRequest();


      // Facets.
      $facetSet = $this->query->getFacetSet();

      /** @var \Solarium\Component\FacetSet $facetSet */
      $facetSet->setSort('count');
      //$facetSet->setLimit(20);
      $facetSet->setMinCount(1);
      $facetSet->setMissing(FALSE);

      # https://wiki.apache.org/solr/SimpleFacetParameters#Multi-Select_Faceting_and_LocalParams
      # https://lucene.apache.org/solr/guide/6_6/faceting.html#Faceting-CombiningFacetQueriesAndFacetRangesWithPivotFacets
      foreach ($this->getIndexKeys() as $key => $solr_key) {
        $facet_field = $facetSet->createFacetField($key)->setField($solr_key);
        $facet_field->setExcludes([$key]);
      }
      /** @var Solarium\QueryType\Select\Query\Query $this->query */
      $response = $this->solrClient->search($this->query);
      static::$results = json_decode($response->getBody());
    }
    return static::$results;
  }

  public function buildQueryParams($params = []) {
    $query_params = [];
    $query_params[] = [
      'key' => "only_type",
      'tag' => "only_type",
      'query' => "ss_type:(" . trim(implode(' OR ', $this->getTypes())) . ")",
    ];

    $index_tags = array_flip($this->getIndexKeys());
    foreach ($params as $k => $param) {
      $v = trim(implode(' OR ', $param));
      $query_params[] = [
        'key' => "{$k}",
        'tag' => $index_tags[$k],
        'query' => "{$k}:({$v})",
      ];
    }
    return $query_params;
  }

  public function alterQuery($params = []) {
    /** @var Solarium\QueryType\Select\Query\Query  $this->query*/
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
        'field_informea_tags' => 'im_field_informea_tags',
      ];
   return $keys;
  }


  public function getTypes() {
    return [
      'treaty',
      'decision',
      'legislation',
      'document',
      'goal',
      'declaration',
      'literature',
    ];
  }
}
