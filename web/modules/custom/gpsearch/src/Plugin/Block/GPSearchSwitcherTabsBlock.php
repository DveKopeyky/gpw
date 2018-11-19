<?php

namespace Drupal\gpsearch\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use \Drupal\Core\Url;

/**
 * Provides a 'GPSearchSwitcherTabsBlock' block.
 *
 * @Block(
 *  id = "gpsearch_switcher_tabs_block",
 *  admin_label = @Translation("Gpsearch switcher tabs block"),
 * )
 */
class GPSearchSwitcherTabsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Routing\CurrentRouteMatch definition.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;
  /**
   * Constructs a new GPSearchSwitcherTabsBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    CurrentRouteMatch $current_route_match
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRouteMatch = $current_route_match;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {

    $routes = [
      'view.gpe_search_page.page_1' => [
        'label' => t('<em>@in</em> <strong>@search_page</strong>', ['@in' => 'in', '@search_page' => 'Global Pact for the Envioronment']),
        'url' => Url::fromRoute('view.gpe_search_page.page_1')->toString(),
      ],
      'informeasearch.informea_search_controller_search' => [
        'label' => t('<em>@in</em> <strong>@search_page</strong>', ['@in' => 'in', '@search_page' => 'InforMEA']),
        'url' => Url::fromRoute('informeasearch.informea_search_controller_search')->toString(),
      ],
    ];

    $routes[$this->currentRouteMatch->getRouteName()]['active'] = TRUE;

    $build = [
      '#theme' => 'gpsearch_switcher_tabs',
      '#routes' => $routes,
      '#cache' => ['max-age' => 0],
    ];
    return $build;
  }

}
