<?php

namespace Drupal\gpw\Plugin\Preprocess;

use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * Pre-processes variables for the "block" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("block")
 */
class Block extends PreprocessBase {

  /**
   * {@inheritdoc}
   */
  public function preprocess(array &$variables, $hook, array $info) {
    $this->addTermPageBlockPrefixSuffix($variables);
    $this->addFeaturedCourseToCoursesBlock($variables);
  }

  /**
   * Add the necessary prefixes and suffixes for the blocks on the glossary term page.
   *
   * @param array $variables
   */
  public function addTermPageBlockPrefixSuffix(array &$variables) {
    if ($variables['base_plugin_id'] == 'views_block') {
      if (\Drupal::routeMatch()->getRouteName() == 'entity.taxonomy_term.canonical'
        && $tid = \Drupal::routeMatch()->getRawParameter('taxonomy_term')
      ) {
        switch ($variables['plugin_id']) {
          case 'views_block:videos-thesaurus_videos':
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
            $prefix = t('Videos tagged with <a href="@search_link">@term_title</a>', [
              '@search_link' => '/search',
              '@term_title' => $term->label(),
            ]);
            $suffix = t('<a href="@search_link">See all videos</a>', [
              '@search_link' => '/search',
            ]);
            break;

          case 'views_block:highlighted_courses-thesaurus_courses':
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
            $prefix = t('Online courses tagged with <a href="@search_link">@term_title</a>', [
              '@search_link' => '/search',
              '@term_title' => $term->label(),
            ]);
            $suffix = t('<a href="@search_link">See all online courses</a>', [
              '@search_link' => '/search',
            ]);
            break;

          case 'views_block:meetings-thesaurus_next_meetings':
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
            $prefix = t('Meetings and events tagged with <a href="@search_link">@term_title</a>', [
              '@search_link' => '/search',
              '@term_title' => $term->label(),
            ]);
            $suffix = NULL;
            break;

          case 'views_block:meetings-thesaurus_past_meetings':
            $meetings_view = Url::fromRoute('view.meetings.page_1');
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
            $prefix = NULL;
            $suffix = t('<a href="@search_link">See all meetings</a>', [
              '@search_link' => $meetings_view->toString(),
            ]);
            break;

          case 'views_block:news-thesaurus_news':
            $news_view = Url::fromRoute('view.news.page_1');
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
            $prefix = t('News tagged with <a href="@search_link">@term_title</a>', [
              '@search_link' => '/search',
              '@term_title' => $term->label(),
            ]);
            $suffix = t('<a href="@search_link">See all news</a>', [
              '@search_link' => $news_view->toString(),
            ]);
            break;

          default:
            $prefix = NULL;
            $suffix = NULL;
        }
        if (!empty($prefix)) {
          $variables['custom_prefix'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['term-page-block-prefix']],
            'prefix' => [
              '#type' => 'markup',
              '#markup' => $prefix,
            ],
          ];
        }
        if (!empty($suffix)) {
          $variables['custom_suffix'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['term-page-block-suffix']],
            'prefix' => [
              '#type' => 'markup',
              '#markup' => $suffix,
            ],
          ];
        }
      }
    }
  }

  /**
   * Add the featured_course variable for the courses block.
   *
   * @param array $variables
   *   The block variables.
   */
  public function addFeaturedCourseToCoursesBlock(array &$variables) {
    if ($variables['base_plugin_id'] == 'views_block'
      && $variables['plugin_id'] == 'views_block:highlighted_courses-thesaurus_courses'
    ) {
      $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'course')
        ->sort('created', 'desc');
      $ids = $query->execute();
      if (empty($ids)) {
        return;
      }
      $course_id = reset($ids);
      $featured_course = Node::load($course_id);
      $rendered_course = \Drupal::entityTypeManager()->getViewBuilder('node')->view($featured_course, 'teaser_2');
      $variables['featured_course'] = $rendered_course;
    }
  }

}
