<?php

namespace Drupal\informeasearch\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\informeasearch\InformeaSearchService;

/**
 * Class InformeaSearchController.
 */
class InformeaSearchController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;
  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;
  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * Drupal\informeasearch\InformeaSearchService definition.
   *
   * @var \Drupal\informeasearch\InformeaSearchService
   */
  protected $informeasearch;

  /**
   * Constructs a new InformeaSearchController object.
   */
  public function __construct(EntityManagerInterface $entity_manager, RequestStack $request_stack, EntityTypeManagerInterface $entity_type_manager, InformeaSearchService $informeasearch) {
    $this->entityManager = $entity_manager;
    $this->requestStack = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
    $this->informeasearch = $informeasearch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('informeasearch')
    );
  }

  /**
   * Search.
   *
   * @return string
   *   Return Hello string.
   */
  public function search() {

/*    $index_keys = $this->informeasearch->getIndexKeys();

    $requests = [];
    if ($request_keys = $this->requestStack->getCurrentRequest()->query->get('f')) {
      foreach ($request_keys as $key) {
        list($k, $v) = explode(':', $key);
          if (array_key_exists($k, $index_keys)) {
            $requests[$index_keys[$k]][] = $v;
          }
       }
    }


    $this->informeasearch->alterQuery($this->informeasearch->buildQueryParams($requests));

    if ($search_api_fulltext = $this->requestStack->getCurrentRequest()->query->get('text')) {
      $this->informeasearch->getQuery()->setQuery($search_api_fulltext);
    }*/

    $this->informeasearch->search();


    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: search')
    ];
  }

}
