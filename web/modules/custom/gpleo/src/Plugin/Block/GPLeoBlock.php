<?php

namespace Drupal\gpleo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'Hello' Block.
 *
 * @Block(
 *   id = "gpleo_block",
 *   admin_label = @Translation("GP LEO Block"),
 *   category = @Translation("TAGS"),
 * )
 */
class GPLeoBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $vid = 'leo';
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);

    $TermsList = [];
    foreach ($terms as $term) {
      $termEtity = Term::load($term->tid);

      if ($termEtity->get('field_leo_show')->getValue()) {
        $link = '';
        if ($termEtity->get('field_leo_link')->getValue()) {
          $link = $termEtity->get('field_leo_link')[0]->getValue()['uri'];
        }
        $TermsList[] = [
          'text' => $term->name,
          'link' => $link,
          'importance' => $termEtity->get('field_leo_importance')[0]->getValue()['value'],
        ];
      }
    }

    return array(
      '#markup' => ' ',
      '#attached' => [
        'library' => ['gpleo/leo-terms'],
        'drupalSettings' => [
          'leoTerms' => [
            'termsList' => $TermsList,
          ],
        ],
      ],
    );
  }

}
