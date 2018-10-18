<?php

namespace Drupal\gpleo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
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
    $vid = 'thesaurus';
    $render = [];
    $tids = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $vid)
      ->condition('field_show_in_term_cloud', 1)
      ->execute();
    $terms = Term::loadMultiple($tids);
    /** @var \Drupal\taxonomy\TermInterface $term */
    foreach ($terms as $term) {
      $link = '';
      if (!empty($term->get('field_thesaurus_link')->getString())) {
        try {
          $link = Url::fromUri($term->get('field_thesaurus_link')->getString());
          $link = $link->toString();
        } catch (\Exception $e) {
          watchdog_exception('GPLeoBlock', $e);
        }
      }
      $render[] = [
        'tid' => $term->id(),
        'text' => $term->label(),
        'link' => $link,
        'importance' => $term->get('field_importance')->getString(),
      ];
    }
    return array(
      '#markup' => ' ',
      '#attached' => [
        'library' => ['gpleo/leo-terms'],
        'drupalSettings' => [
          'leoTerms' => [
            'termsList' => $render,
          ],
        ],
      ],
    );
  }

}
