<?php

namespace Drupal\informeasearch\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\informeasearch\InformeaSearchService;
use Drupal\facets\Entity\Facet;
use Drupal\facets\Result\Result;
use Drupal\Core\Url;



/**
 * Global search block shown in the website's header
 *
 * @Block(
 *   id = "informeasearch_facets_block",
 *   admin_label = @Translation("Informea Search Facets"),
 *   category = @Translation("Search"),
 * )
 */
class InformeaSearchFacetsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\informeasearch\InformeaSearchService definition.
   *
   * @var \Drupal\informeasearch\InformeaSearchService
   */
  protected $informeasearch;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, InformeaSearchService $informeasearch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->informeasearch = $informeasearch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('informeasearch')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function build() {
    if (!Facet::load('informeaseach_facet')) {
      $facet = Facet::create([
        'id' => 'informeaseach_facet',
        'name' => 'InformeaSearch facet',
      ])->save();
    }

    $facet = Facet::load('informeaseach_facet');

    //$facet->setFacetSourceId($facet_source);
    //$facet->setFieldIdentifier($field);
    //$facet->setUrlAlias('vasea');

    /*$result_lower = new Result($facet, 5, '5', 1);
    $result_higher = new Result($facet, 150, '150', 1);
    $facet->setResults([$result_lower, $result_higher]);*/

    $results = [];
    $facets = $this->informeasearch->facets();

    //dpm($facets);

    if ($facets->type) {
      for( $i=0; $i <=count($facets->type); $i += 2 ) {
        if (isset($facets->type[$i+1])) {
          $res = new Result($facet, $facets->type[$i], $facets->type[$i], $facets->type[$i+1]);
          $res->setUrl(new Url('informeasearch.informea_search_controller_search'));
          //$res->setActiveState(TRUE);
          $results[] = $res;
        }
      }
    }
    $facet->setResults($results);

    $facet->setWidget('links', ['show_numbers' => TRUE]);
  /*  $facet->addProcessor([
      'processor_id' => 'url_processor_handler',
      'weights' => ['pre_query' => -10, 'build' => -10],
      'settings' => [],
    ]);*/
    /*$facet->setEmptyBehavior(['behavior' => 'none']);
    $facet->setOnlyVisibleWhenFacetSourceIsVisible(TRUE);*/

    $widget = $facet->getWidgetInstance();
    $build = $widget->build($facet);
    $build['#cache']['max-age'] = 0;
    return $build;
  }
}
