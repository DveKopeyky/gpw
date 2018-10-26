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

          case 'views_block:documents-thesaurus_documents':
            $view = $variables['content']['#view'];
            $results = (count($view->result));

            // results are now stored as an array in $view->result
            $view->build('patient');
            $q = $view->query->query()->countQuery()->execute()->fetchAssoc();
            $total_count = reset($q);

            $r = $total_count - $results;
            $remaining = ($r > 0) ? $r : NULL;

            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
            $inner_prefix = t('Documents tagged with <a href="@search_link">@term_title</a>', [
              '@search_link' => '/search',
              '@term_title' => $term->label(),
            ]);
            if ($remaining) {
              $inner_suffix = t('<a class="see-all" href="@search_link">See more (@remaining)</a>', [
                '@search_link' => '/search',
                '@remaining' => $remaining,
              ]);
            }
            $variables['#cache']['max-age'] = 0;
            break;

          case 'views_block:videos-thesaurus_videos':
            $search_view_link = Url::fromRoute('view.gpe_search_page.page_1')->toString();
            $type = 'video';
            $search_view_link .= "?f[0]=type%3A$type";
            $search_view_link_with_tag = $search_view_link . "&f[1]=tags%3A$tid";

            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
            $inner_prefix = t('Videos tagged with <a href="@search_link">@term_title</a>', [
              '@search_link' => $search_view_link_with_tag,
              '@term_title' => $term->label(),
            ]);
            $inner_suffix = t('<a class="see-all" href="@search_link">See all videos</a>', [
              '@search_link' => $search_view_link,
            ]);
            break;

          case 'views_block:highlighted_courses-thesaurus_courses':
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
            $search_view_link = Url::fromRoute('view.gpe_search_page.page_1')->toString();
            $type = 'highlighted_course';
            $search_view_link .= "?f[0]=type%3A$type";
            $search_view_link_with_tag = $search_view_link . "&f[1]=tags%3A$tid";

            $inner_prefix = t('Online courses tagged with <a href="@search_link">@term_title</a>', [
              '@search_link' => $search_view_link_with_tag,
              '@term_title' => $term->label(),
            ]);
            $inner_suffix = t('<a class="see-all" href="@search_link">See all online courses</a>', [
              '@search_link' => $search_view_link,
            ]);
            break;

          case 'views_block:meetings-thesaurus_next_meetings':
            $search_view_link = Url::fromRoute('view.gpe_search_page.page_1')->toString();
            $type = 'meeting';
            $search_view_link .= "?f[0]=type%3A$type" . "&f[1]=tags%3A$tid";

            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
            $variables['outer_prefix'] = [
              '#theme' => 'icon_prefix',
              '#icon' => 'tag',
              '#prefix' => '<div class="h2 block-title block-title--meetings">',
              '#suffix' => t('</div><div class="term-page-block-prefix form-group">Meetings and events tagged with <a href="@search_link">@term_title</a></div>', [
                '@search_link' => $search_view_link,
                '@term_title' => $term->label(),
              ]),
              '#content' => 'Meetings',
            ];

            $inner_suffix = NULL;
            break;

          case 'views_block:meetings-thesaurus_past_meetings':
            $search_view_link = Url::fromRoute('view.gpe_search_page.page_1')->toString();
            $type = 'meeting';
            $search_view_link .= "?f[0]=type%3A$type";

            $meetings_view = Url::fromRoute('view.meetings.page_1');
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
            $inner_prefix = NULL;
            $inner_suffix = NULL;

            $variables['outer_suffix'] = [
              '#type' => 'container',
              '#attributes' => ['class' => ['term-page-block-suffix']],
              'prefix' => [
                '#type' => 'markup',
                '#markup' => t('<a class="see-all" href="@search_link">See all meetings</a>', [
                  '@search_link' => $search_view_link,
                ]),
              ],
            ];

            break;

          case 'views_block:news-thesaurus_news':
            $news_view = Url::fromRoute('view.news.page_1');
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
            $inner_prefix = t('News tagged with <a href="@search_link">@term_title</a>', [
              '@search_link' => '/search',
              '@term_title' => $term->label(),
            ]);
            $inner_suffix = t('<a class="see-all" href="@search_link">See all news</a>', [
              '@search_link' => $news_view->toString(),
            ]);
            break;

          default:
            $inner_prefix = NULL;
            $inner_suffix = NULL;
        }
        if (!empty($inner_prefix)) {
          $variables['inner_prefix'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['term-page-block-prefix']],
            'prefix' => [
              '#type' => 'markup',
              '#markup' => $inner_prefix,
            ],
          ];
        }
        if (!empty($inner_suffix)) {
          $variables['inner_suffix'] = [
            '#type' => 'container',
            '#attributes' => ['class' => ['term-page-block-suffix']],
            'prefix' => [
              '#type' => 'markup',
              '#markup' => $inner_suffix,
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
