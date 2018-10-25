<?php

namespace Drupal\gpleo;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\TermStorage;

/**
 * Class GPLeoTerms.
 */
class GPLeoTerms {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  protected $taxonomy_term;

  protected $term = NULL;
  /**
   * Constructs a new GPLeoTerms object.
   */
  public function __construct(RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;

    if ($this->routeMatch->getRouteName() == 'entity.taxonomy_term.canonical'
      && $tid =$this->routeMatch->getRawParameter('taxonomy_term')
    ) {
      $this->taxonomy_term = $this->entityTypeManager->getStorage('taxonomy_term');
      $this->term = $this->taxonomy_term->load($tid);
    }
  }

  public function id() {
    return $this->term ? $this->term->id() : NULL;
  }

  public function parents(){
    $items = $this->taxonomy_term->loadAllParents($this->term->id());
    $list = [];
    foreach ($items as $term) {
      if( $term->id() != $this->term->id()) {
        $list[$term->id()] = $term->label();
      }
    }
    return $list;
  }

  public function children(){
    $items = $this->taxonomy_term->loadChildren($this->term->id());
    $list = [];
    foreach ($items as $term) {
      if( $term->id() != $this->term->id()) {
        $list[$term->id()] = $term->label();
      }
    }
    return $list;
  }

}
