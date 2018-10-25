<?php

namespace Drupal\gpleo\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a 'GP Informea Content Block' Block.
 *
 * @Block(
 *   id = "gpinformea_content_block",
 *   admin_label = @Translation("GP Informea Content Block"),
 *   category = @Translation("TAGS"),
 * )
 */

class GPInformeaContentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#markup' => '<h2>Explore in informea</h2><br/><em>Placeholder for content imported from informea via API call.</em>',
    );
  }


  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if ($this->routeMatch->getRouteName() == 'entity.taxonomy_term.canonical'
      && $tid =$this->routeMatch->getRawParameter('taxonomy_term')
    ) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
      if ($term->bundle() == 'thesaurus') {
        return AccessResult::allowed()->addCacheContexts(['route.name', 'taxonomy_term']);
      }
    }
    return AccessResult::forbidden();
  }

}
