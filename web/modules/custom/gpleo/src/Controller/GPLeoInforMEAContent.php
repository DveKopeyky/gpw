<?php

namespace Drupal\gpleo\Controller;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\taxonomy\TermInterface;

/**
 * GP LEO Tags content controller.
 */
class GPLeoInforMEAContent extends ControllerBase {

  /**
   * Building query to the informea server.
   */
  private function buildQuery($content_type, $informea_tid, $fields = [], $page = 1, $limit = 5, $additional_query_params = '') {
    // @TODO replace it with variables when settings form will be ready.
    $server_url = 'http://informea:solr6@search.informea.org/solr/informea/select';
    $offset = ($page - 1) * $limit;

    $request_data[] = $server_url . '?';
    $request_data[] = 'indent=on';
    $request_data[] = 'q=*:*';
    $request_data[] = 'wt=json';
    $request_data[] = 'fq=is_status:1';
    $request_data[] = 'fq=ss_type:' . $content_type;
    if ($informea_tid) {
      $request_data[] = 'fq=im_field_informea_tags:' . $informea_tid;
    }
    if (count($fields)) {
      $request_data[] = 'fl=' . implode(',', $fields);
    }
    $request_data[] = 'rows=' . $limit;
    $request_data[] = 'start=' . $offset;
    if ($additional_query_params) {
      $request_data[] = $additional_query_params;
    }

    $request_link = implode('&', $request_data);

    return $request_link;
  }

  /**
   * Get data from the URL.
   */
  private function getRequestData($url) {
    $client = \Drupal::httpClient();

    try {
      $response = $client->get($url);
      $result = (string) $response->getBody();
      if (empty($result)) {
        return FALSE;
      }
    }
    catch (RequestException $e) {
      return FALSE;
    }

    return json_decode($result);
  }

  /**
   * Render result markup.
   */
  private function customRender($template_name, $params) {
    $options = [
      '#theme' => $template_name,
      '#params' => $params,
    ];
    $result = \Drupal::service('renderer')->render($options);

    return $result;
  }

  /**
   * Retrun list of fields for Query result.
   */
  private function getDefaultQueryFields() {
    $fields = [
      'is_nid',
      'tm_field_decision_number',
      'ss_search_api_url',
      'tm_title',
      'ss_type',
      'content',
    ];

    return $fields;
  }

  /**
   * Prepare data for template.
   */
  private function prepareData(TermInterface $taxonomy_term, $fields, $content_type, $additional_query_params = '', $custom_limit = 0) {
    $informea_tid = $taxonomy_term->get('field_informea_tid')->getString();
    if ($informea_tid) {
      // @TODO Replace it with variable, when settings form will be available.
      $limit = 5;
      if ($custom_limit) {
        $limit = $custom_limit;
        $page = 1;
      }
      else {
        $page = \Drupal::request()->query->get('page');
        if (!$page || intval($page) <= 0) {
          $page = 1;
        }
      }

      $url = self::buildQuery($content_type, $informea_tid, $fields, $page, $limit, $additional_query_params);
      $data = self::getRequestData($url);

      $template_data = (array) $data->response;
      $template_data['page'] = $page;
    }
    else {
      $template_data = [];
      $template_data['numFound'] = 0;
    }
    $template_data['term_name'] = $taxonomy_term->getName();
    $template_data['term_link'] = $taxonomy_term->toLink();
    $template_data['tid'] = $informea_tid;
    $template_data['limit'] = $limit;

    return $template_data;
  }

  /**
   * Prepare Treaty items for our tree.
   */
  private function prepareTreatyParentsPagedList($informea_tid, $content_type, $limit) {
    // @TODO Replace it with variable.
    $url = 'http://informea:solr6@search.informea.org/solr/informea/select';
    $url .= '?q=*:*';
    $url .= '&wt=json';
    $url .= '&fq=ss_type:' . $content_type;
    $url .= '&fq=im_field_informea_tags:' . $informea_tid;
    // To hide fields in response.
    $url .= '&fl=none';
    $url .= '&facet=true';
    $url .= '&facet.field=im_field_treaty';
    $url .= '&rows=1';

    $data = self::getRequestData($url);

    $facet_counts = $data->facet_counts;

    $facet_treaty_ids = $facet_counts->facet_fields->im_field_treaty;
    $treaty_nids = [];
    for ($i = 0; $i < count($facet_treaty_ids); $i += 2) {
      if ($facet_treaty_ids[$i + 1] === 0) {
        break;
      }
      $treaty_page = floor($i / (2 * $limit)) + 1;
      $treaty_nid = $facet_treaty_ids[$i];
      $treaty_nids[$treaty_page]['items'][$treaty_nid] = [
        'items_count' => $facet_treaty_ids[$i + 1],
        'nid' => $treaty_nid,
      ];
      // Better set it here to get all treaty data.
      $treaty_nids[$treaty_page]['nid_list'][$treaty_nid] = $treaty_nid;
    }
    $treaty_count = $i / 2;

    $result = [
      'numFound' => $treaty_count,
      'treaty_nids' => $treaty_nids,
    ];

    return $result;
  }

  /**
   * Prepare parent treaty level for the tree.
   */
  private function prepareTreatyLevel($treaty_nids, $page, $limit) {
    $treaty_tree = [];
    if (isset($treaty_nids[$page])) {
      // Get all treaty data.
      $url = 'http://informea:solr6@search.informea.org/solr/informea/select';
      $url .= '?q=*:*';
      $url .= '&wt=json';
      $url .= '&fq=ss_type:treaty';
      $url .= '&fq=is_nid:' . implode(' OR is_nid:', $treaty_nids[$page]['nid_list']);
      // @TODO Insert additional fields for treaty here.
      $url .= '&fl=is_nid,tm_title,ss_search_api_url';
      $url .= '&rows=' . $limit;
      // It is always starts from zero, because elements already separated by pages.
      $url .= '&start=0';

      $treaty_data = self::getRequestData($url);

      foreach ($treaty_data->response->docs as $treaty_item) {
        $treaty_nid = $treaty_item->is_nid;
        $treaty_tree['docs'][$treaty_nid] = [
          '#theme' => 'gpleo_treaty_text_template',
          '#treaty_item' => $treaty_item,
          'items_count' => $treaty_nids[$page]['items'][$treaty_nid]['items_count'],
        ];
      }
    }

    return $treaty_tree;
  }

  /**
   * Prepare tagged treaty articles and treaty paragraphs.
   */
  private function prepareTaggedTreatyArticleAndPargraphLevel(TermInterface $taxonomy_term, &$treaty_tree) {
    // Get all pargraphs and articles taged with this term.
    foreach ($treaty_tree['docs'] as $treaty_nid => $treaty_item_data) {
      $additional_query_params = 'fq=im_field_treaty:' . $treaty_nid;

      $treaty_children_data = self::prepareData($taxonomy_term, [], 'treaty_article OR ss_type:treaty_paragraph', $additional_query_params, $treaty_item_data['items_count']);

      if ($treaty_children_data['numFound']) {
        foreach ($treaty_children_data['docs'] as $delta => $doc_item) {
          switch ($doc_item->ss_type) {
            case 'treaty_paragraph':
              $article_nid = $doc_item->is_field_parent_treaty_article;
              $treaty_tree['docs'][$treaty_nid]['#article_docs'][$article_nid]['#paragraphs'][$doc_item->is_field_sorting_order] = [
                '#theme' => 'gpleo_treaty_paragraph_template',
                '#paragraph' => $doc_item,
              ];
              break;

            case 'treaty_article':
              $article_nid = $doc_item->is_nid;
              $treaty_tree['docs'][$treaty_nid]['#article_docs'][$article_nid]['#article_item'] = $doc_item;
              break;

            default:
              continue;

          }
          $treaty_tree['docs'][$treaty_nid]['#article_docs'][$article_nid]['#theme'] = 'gpleo_treaty_article_template';
        }
      }
    }
  }

  /**
   * Fill data for paragraphs untagged parent articles.
   */
  private function prepareMissedTreatyArticles(&$treaty_tree) {
    // Fill empty Articles data.
    $missed_articles = [];
    $missed_articles_treaty_nids = [];
    foreach ($treaty_tree['docs'] as $treaty_nid => $treaty_docs_data) {
      foreach ($treaty_docs_data['#article_docs'] as $article_nid => $article_data) {
        if (!isset($article_data['#article_item']->is_nid)) {
          $missed_articles[$article_nid] = $article_nid;
          $missed_articles_treaty_nids[$treaty_nid] = $treaty_nid;
        }
      }
    }

    $missed_articles_count = count($missed_articles);
    if ($missed_articles_count) {
      $additional_query_params = 'fq=im_field_treaty:' . implode(' OR im_field_treaty:', $missed_articles_treaty_nids);
      $additional_query_params .= '&fq=is_nid:' . implode(' OR is_nid:', $missed_articles);

      $url = self::buildQuery('treaty_article', 0, [], 1, $missed_articles_count, $additional_query_params);
      $treaty_articles_data = self::getRequestData($url);

      foreach ($treaty_articles_data->response->docs as $article) {
        $treaty_tree['docs'][$article->im_field_treaty[0]]['#article_docs'][$article->is_nid]['#article_item'] = $article;
      }
    }
  }

  /**
   * Prepare treaty rendrable tree.
   */
  private function prepareTreatyTree(TermInterface $taxonomy_term) {
    $content_type = 'treaty_paragraph OR ss_type:treaty_article';
    $limit = 5;
    $informea_tid = $taxonomy_term->get('field_informea_tid')->getString();
    $page = \Drupal::request()->query->get('page');
    if (!$page || intval($page) <= 0) {
      $page = 1;
    }

    // 1st request to get treaty.
    $treaty_hierarchy_data = self::prepareTreatyParentsPagedList($informea_tid, $content_type, $limit);
    $treaty_nids = $treaty_hierarchy_data['treaty_nids'];

    // 2nd request to get treaty data.
    $treaty_tree = self::prepareTreatyLevel($treaty_nids, $page, $limit);
    $treaty_tree['numFound'] = $treaty_hierarchy_data['numFound'];

    // 3rd request to get tagged article and paragraph data.
    self::prepareTaggedTreatyArticleAndPargraphLevel($taxonomy_term, $treaty_tree);

    // 4th request to get untagged articles with tagged paragraphs.
    self::prepareMissedTreatyArticles($treaty_tree);

    $treaty_tree['start'] = ($page - 1) * $limit;
    $treaty_tree['page'] = $page;
    $treaty_tree['term_name'] = $taxonomy_term->getName();
    $treaty_tree['term_link'] = $taxonomy_term->toLink();
    $treaty_tree['tid'] = $informea_tid;
    $treaty_tree['limit'] = $limit;

    return $treaty_tree;
  }

  /**
   * Get data from informea server for Treaty texts.
   */
  public function getTreatyText(Request $request, TermInterface $taxonomy_term) {
    $template_data = self::prepareTreatyTree($taxonomy_term);
    // @TODO replace it with right link.
    $template_data['global_link'] = 'Treaty texts';

    // Prepare output.
    $template_name = 'gpleo_treaty_block_template';
    $markup = self::customRender($template_name, $template_data);

    $response = [
      'markup' => $markup,
    ];

    return new JsonResponse($response);
  }

  /**
   * Get data from informea server.
   */
  public function getTreatyDecisions(Request $request, TermInterface $taxonomy_term) {
    $content_type = 'decision';
    $fields = self::getDefaultQueryFields();

    $template_data = self::prepareData($taxonomy_term, $fields, $content_type);
    // Prepare templates.
    $docs = $template_data['docs'];
    $template_data['docs'] = [];
    foreach ($docs as $delta => $doc_item) {
      $template_data['docs'][] = [
        '#theme' => 'gpleo_decision_template',
        '#doc_item' => $doc_item,
      ];
    }
    // @TODO replace it with right link.
    $template_data['global_link'] = 'Treaty decisions';

    $template_name = 'gpleo_decision_block_template';

    // May be better return JSON without render.
    $markup = self::customRender($template_name, $template_data);

    $response = [
      'markup' => $markup,
    ];

    return new JsonResponse($response);
  }

  /**
   * Get data from informea server.
   */
  public function getDocumentsAndLiterature(Request $request, TermInterface $taxonomy_term) {
    $content_type = 'document OR ss_type:literature';
    $fields = [];

    $template_data = self::prepareData($taxonomy_term, $fields, $content_type);

    // Prepare templates.
    $docs = $template_data['docs'];
    $template_data['docs'] = [];
    foreach ($docs as $delta => $doc_item) {
      // Very long content fix.
      $doc_item->content = Unicode::truncate($doc_item->content, 100, TRUE, TRUE);
      $template_data['docs'][] = [
        '#theme' => 'gpleo_' . $doc_item->ss_type . '_template',
        '#doc_item' => $doc_item,
      ];
    }
    // @TODO replace it with right link.
    $template_data['global_link'] = 'Documents';

    $template_name = 'gpleo_document_block_template';

    // May be better return JSON without render.
    $markup = self::customRender($template_name, $template_data);

    $response = [
      'markup' => $markup,
    ];

    return new JsonResponse($response);
  }

  /**
   * Get data from informea server.
   */
  public function getGoalsAndDeclarations(Request $request, TermInterface $taxonomy_term) {
    $content_type = 'goal';
    $fields = self::getDefaultQueryFields();
    $informea_tid = $taxonomy_term->get('field_informea_tid')->getString();
    $limit = 5;
    $page = \Drupal::request()->query->get('page');
    if (!$page || intval($page) <= 0) {
      $page = 1;
    }

    $url = self::buildQuery($content_type, $informea_tid, $fields, $page, $limit);
    $template_data = self::getRequestData($url);
    // @TODO replace it with right link.
    $template_data['global_link'] = 'Goals';

    $template_name = 'gpleo_goal_block_template';

    // May be better return JSON without render.
    $markup = self::customRender($template_name, $template_data);

    $response = [
      'markup' => $markup,
    ];

    return new JsonResponse($response);
  }

}
