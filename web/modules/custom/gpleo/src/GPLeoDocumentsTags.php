<?php

namespace Drupal\gpleo;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\eck\Entity\EckEntity;

/**
 * Class GPLeoDocumentsTags.
 */
class GPLeoDocumentsTags {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * Constructs a new GPLeoDocumentsTags object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
    * Import content from /content/sync directory.
    *
    * @param int $tid
    *   Thesaurus taxonomy term id.
    *
    * @param int nid
    *  Document node id.
    *
    */

  public function documentTagsPageNumber($tid, $nid) {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $node = $node_storage->load($nid);
    if ($node && $field_tags = $node->get('field_tags')) {
      $tags = array_column($node->field_tags->getValue(), 'target_id');
      foreach ($tags as $tag_tid) {
        $tag = EckEntity::load($tag_tid);
        if ($field_tags = $tag->field_tags->getValue()) {
          if (isset($field_tags[0]['target_id']) && $field_tags[0]['target_id'] == $tid) {
            if ($field_page_number = $tag->field_page_number->value) {
              return $field_page_number;
            }
          }
        }
      }
    }
    return NULL;
  }

}
