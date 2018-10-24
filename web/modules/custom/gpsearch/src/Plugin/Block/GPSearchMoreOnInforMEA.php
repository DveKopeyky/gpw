<?php

/**
 * @file
 *
 */

namespace Drupal\gpsearch\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Global search block shown in the website's header
 *
 * @Block(
 *   id = "more_on_informea_block",
 *   admin_label = @Translation("See more results on InforMEA"),
 *   category = @Translation("Search"),
 * )
 */
class GPSearchMoreOnInforMEA extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $label = $config['label_display'] ? $config['label'] : NULL;
    $ret = [
      'header' => [
        '#markup' => '<div><img src="/themes/custom/gpw/dist/images/search-more-on-informea.png" /></div>'
      ],
      'link' => [
        '#attributes' => ['class' => 'more-link'],
        '#type' => 'link',
        '#title' => new TranslatableMarkup('See more results in InforMEA'),
        '#url' => Url::fromUri('https://www.informea.org/search', ['attributes' => ['target' => '_blank']])
      ]
    ];
    return $ret + ['#cache' => ['max-age' => 0]];
  }
}
