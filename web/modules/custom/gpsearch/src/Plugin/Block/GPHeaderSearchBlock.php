<?php

/**
 * @file
 * Contains Drupal\gpsearch\Plugin\Block\GPHeaderSearchBlock
 */

namespace Drupal\gpsearch\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use \Drupal\Core\Url;

/**
 * Global search block shown in the website's header
 *
 * @Block(
 *   id = "header_search_block",
 *   admin_label = @Translation("Header Search Block"),
 *   category = @Translation("Search"),
 * )
 */
class GPHeaderSearchBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $action =  Url::fromRoute('view.gpe_search_page.page_1')->toString();
    $placeholder = t('Search in Global Pact Website');

    $current_route = \Drupal::routeMatch()->getRouteName();
    if ($current_route == 'informeasearch.informea_search_controller_search') {
      $action = Url::fromRoute('informeasearch.informea_search_controller_search')->toString();
      $placeholder = t('Search in informea');
    }

    $block = [
      '#theme' => 'gpsearch_block_template',
      '#attached' => [
        'library' => ['gpsearch/autocomplete'],
      ],
      '#placeholder' => $placeholder,
      '#action' => $action,
      '#attributes' => [
        'class' => [
          'gpsearch-fulltext-block',
          'row',
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
    return $block;
  }
}
