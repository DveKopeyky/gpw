<?php

namespace Drupal\informeasearch\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\informeasearch\InformeaSearchService;
use Drupal\informeasearch\InformeaSearchFacetsService;


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
    $search_results = $this->informeasearch->search()->response;


    $types = InformeaSearchFacetsService::facetLabels('type');
    $topics = InformeaSearchFacetsService::facetLabels('field_mea_topic');


    $results = [];
    if ($search_results->numFound ){
      $template_data = (array) $search_results;

      // Prepare templates.
      $docs = $template_data['docs'];
      foreach ($docs as $delta => $doc_item) {


        if (isset($types[$doc_item->ss_type])) {
          $doc_item->type = $types[$doc_item->ss_type];
        }
        $doc_topics = [];
        if ($doc_item->im_field_mea_topic) {
          foreach ($doc_item->im_field_mea_topic as $topic) {
            if (isset($topics[$topic])) {
              $doc_topics[] = $topics[$topic];
            }
          }
          $doc_item->topics = implode(', ', $doc_topics);
        }

        $results['docs'][] = [
            "#theme" => "informeasearch_item",
            "#doc_item" => $doc_item,
          ];
      }
      // Initialize the pager;
      pager_default_initialize($search_results->numFound, 10);
    }
    else {
      $results = ['#markup' => '<p class="text-center text-muted">' . t('There are no search results.') . '</p>',];
    }

    return [
      'results' => $results,
      'pager' => ['#type' => 'pager'],
    ];

  }

}
