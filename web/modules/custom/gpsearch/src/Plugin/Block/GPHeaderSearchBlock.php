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

    $block = [
      '#theme' => 'gpsearch_block_template',
      '#attached' => [
        'library' => ['gpsearch/autocomplete'],
      ],
      '#action' => Url::fromRoute('view.gpe_search_page.page_1')->toString(),
    ];

    return $block;
  }
}
