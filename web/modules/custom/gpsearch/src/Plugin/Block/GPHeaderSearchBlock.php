<?php

/**
 * @file
 * Contains Drupal\gpsearch\Plugin\Block\GPHeaderSearchBlock
 */

namespace Drupal\gpsearch\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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
    $label = $config['label_display'] ? $config['label'] : NULL;
    $form = \Drupal::formBuilder()->getForm('Drupal\gpsearch\Form\GPHeaderSearchForm', $label);
    unset($form['form_build_id']);
    unset($form['form_id']);
    $form['#cache'] = ['max-age' => 0];
    return $form;
  }
}
