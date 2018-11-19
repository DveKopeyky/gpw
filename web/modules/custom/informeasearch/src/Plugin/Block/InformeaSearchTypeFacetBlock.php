<?php

namespace Drupal\informeasearch\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\informeasearch\InformeaSearchFacetsService;

/**
 * Global search block shown in the website's header
 *
 * @Block(
 *   id = "informeasearch_type_facet_block",
 *   admin_label = @Translation("Informea Search Type Facet"),
 *   category = @Translation("Informea Search Facets"),
 * )
 */
class InformeaSearchTypeFacetBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\informeasearch\InformeaSearchFacetsService definition.
   *
   * @var \Drupal\informeasearch\InformeaSearchFacetsService
   */
  protected $facets;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, InformeaSearchFacetsService $facets) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->facets = $facets;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('informeasearch.facets')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->facets->facetBuild('informeaseach_type_facet', 'type');
  }
}
