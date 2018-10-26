<?php

/**
 * @file
 *
 */

namespace Drupal\gpsearch\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
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
    $links = [
      'Treaty text' => 'https://www.informea.org/en/search?f%5B0%5D=type%3Atreaty',
      'Treaty decisions' => 'https://www.informea.org/en/search?f%5B0%5D=type%3Acourt_decisions',
      'Legislation' => 'https://www.informea.org/en/search?f%5B0%5D=type%3Alegislation',
      'Documents and literature' => 'https://www.informea.org/en/search?f%5B0%5D=type%3Adocument&f%5B1%5D=type%3Aliterature',
      'Goals and declarations' => 'https://www.informea.org/en/search?f%5B0%5D=type%3Agoal&f%5B1%5D=type%3Adeclaration',
    ];
    $ret = [
      'header' => [
        '#markup' => '<div><img src="/themes/custom/gpw/dist/images/search-more-on-informea.png" /></div>'
      ],
      'link' => [
        '#attributes' => ['class' => 'more-link'],
        '#type' => 'link',
        '#title' => new TranslatableMarkup('See more results in InforMEA'),
        '#url' => Url::fromUri('https://www.informea.org/search', ['attributes' => ['target' => '_blank']])
      ],
    ];

    // @todo: make this prettier.
    foreach ($links as $title => $url) {
      $ret['links'][] = [
        '#type' => 'container',
        'link' => [
          '#markup' => '<a target="_blank" href="' . $url . '">' . $this->t($title) . '<i class="fas fa-external-link-alt"></i></a>',
        ],
      ];
    }
    return $ret + ['#cache' => ['max-age' => 0]];
  }
}
