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
    $vid = 'leo';
    $render = [];
    $tids = \Drupal::database()
      ->select('taxonomy_term__field_leo_show', 'a')
      ->fields('a', ['entity_id'])
      ->condition('a.bundle', $vid)
      ->condition('a.field_leo_show_value', 1)
      ->execute()
      ->fetchCol();
    $terms = Term::loadMultiple($tids);
    /** @var \Drupal\taxonomy\TermInterface $term */
    foreach ($terms as $term) {
      $link = '';
      if (!empty($term->get('field_leo_link')->getString())) {
        try {
          $link = Url::fromUri($term->get('field_leo_link')->getString());
          $link = $link->toString();
        } catch (\Exception $e) {
          watchdog_exception('GPLeoBlock', $e);
        }
      }
      $render[] = [
        'tid' => $term->id(),
        'text' => $term->label(),
        'link' => $link,
        'importance' => $term->get('field_leo_importance')->getString(),
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
