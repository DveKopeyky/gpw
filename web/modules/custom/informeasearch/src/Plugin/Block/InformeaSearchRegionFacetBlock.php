<?php

namespace Drupal\informeasearch\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\informeasearch\InformeaSearchService;

/**
 * Global search block shown in the website's header
 *
 * @Block(
 *   id = "informeasearch_region_facet_block",
 *   admin_label = @Translation("Informea Search Region Facet"),
 *   category = @Translation("Informea Search Facets"),
 * )
 */
class InformeaSearchRegionFacetBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    return $this->informeasearch->facetBuild('informeaseach_region_facet', 'field_region');
  }
}
