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
 *   id = "informeasearch_tags_facet_block",
 *   admin_label = @Translation("Informea Search Tags Facet"),
 *   category = @Translation("Informea Search Facets"),
 * )
 */
class InformeaSearchTagsFacetBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    return $this->informeasearch->facetBuild('informeaseach_tags_facet', 'field_informea_tags');
  }
}
