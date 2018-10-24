<?php

namespace Drupal\gpleo\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'GP Informea Content Block' Block.
 *
 * @Block(
 *   id = "gpinformea_content_block",
 *   admin_label = @Translation("GP Informea Content Block"),
 *   category = @Translation("TAGS"),
 * )
 */

class GPInformeaContentBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#markup' => '<h2>Explore in informea</h2><br/><em>Placeholder for content imported from informea via API call.</em>',
    );
  }

}
