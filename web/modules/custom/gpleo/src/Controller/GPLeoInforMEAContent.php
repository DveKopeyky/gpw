<?php

namespace Drupal\gpleo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpFoundation\Request;
use Drupal\taxonomy\TermInterface;

/**
 * GP LEO Tags content controller.
 */
class GPLeoInforMEAContent extends ControllerBase {

  /**
   * Building query to the informea server.
   *
   * @return String
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
      $request_data[] = 'fl=' . join(',', $fields);
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
      'tm_field_decision_number',
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
      }

      $page = \Drupal::request()->query->get('page');
      if (!$page || intval($page) <= 0) {
        $page = 1;
      }

      $url = self::buildQuery($content_type, $informea_tid, $fields, $page, $limit, $additional_query_params);
      $data = self::getRequestData($url);

      $template_data = (array)$data->response;
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
   * Get data from informea server for Treaty texts.
   *
   * @return JSON.
   */
  public function getTreatyText(Request $request, TermInterface $taxonomy_term) {
    // Get treaty ids. First request.
    $content_type = 'treaty_paragraph OR treaty_article';
    $limit = 5;
    $fields = [
//      'im_field_treaty',
    ];
    $informea_tid = $taxonomy_term->get('field_informea_tid')->getString();

//    $template_data = self::prepareData($taxonomy_term, $fields, $content_type);
    $url = 'http://informea:solr6@search.informea.org/solr/informea/select';
    $url .= '?q=*:*';
    $url .= '&wt=json';
    $url .= '&fq=ss_type:' . $content_type;
    $url .= '&fq=im_field_informea_tags:' . $informea_tid;
    $url .= '&fl=none'; // To hide fields in response.
    $url .= '&facet=true';
    $url .= '&facet.field=im_field_treaty';
    $url .= '&rows=1';

    $data = self::getRequestData($url);

    $facet_counts = $data->facet_counts;

    $facet_treaty_ids = $facet_counts->facet_fields->im_field_treaty;
    $treaty_count = 0;
    $treaty_nids = [];
    for($i = 0; $i < count($facet_treaty_ids); $i += 2) {
      $treaty_page = floor($i / (2 * $limit)) + 1;
      $treaty_nids[$treaty_page][] = [
        'items_count' => $facet_treaty_ids[$i + 1],
        'nid' => $facet_treaty_ids[$i],
      ];
      if ($facet_treaty_ids[$i + 1] === 0) {
        $treaty_count = $i / 2;
        break;
      }
    }

    $page = \Drupal::request()->query->get('page');
    if (!$page || intval($page) <= 0) {
      $page = 1;
    }
    if (isset($treaty_nids[$page])) {
      $treaty_page_data = $treaty_nids[$page];
      foreach($treaty_page_data as $treaty_item_data) {
        // Get all articles.
//        is_field_parent_treaty_article
//        is_field_sorting_order
//        im_field_treaty

        $treaty_data = self::prepareData($taxonomy_term, $fields, $content_type, 'fq=im_field_treaty:' . $treaty_item_data['nid'], $treaty_item_data['items_count']);
        $treaty_data = $treaty_data;
        $docs_data = $treaty_data['docs'];
        $docs = [];

        foreach ($docs_data as $delta => $doc_item) {
          switch ($doc_item->ss_type) {
            case 'treaty_paragraph':
              $docs[$doc_item->is_field_parent_treaty_article]['paragraphs'][$doc_item->is_field_sorting_order] = (array)$doc_item;
              $missed_articles[] = $doc_item->is_field_parent_treaty_article;
              break;

            case 'treaty_article':
              $docs[$doc_item->is_nid]['article'] = (array)$doc_item;
              break;

            default:
              continue;

          }
        }

        // Fill empty Articles data.
        $missed_articles = [];
        foreach ($docs as $article_nid => $article_data) {
          if (!isset($docs[$article_nid]['article']['is_nid'])) {
            $missed_articles[] = $article_nid;
          }
        }
        $missed_articles_count = count($missed_articles);
        if ($missed_articles_count) {
          $additional_query_params = 'fq=im_field_treaty:' . $treaty_item_data['nid'];
          $additional_query_params .= '&fq=is_nid:' . implode(' OR is_nid:', $missed_articles);

          $url = self::buildQuery('treaty_article', 0, $fields, 1, $missed_articles_count, $additional_query_params);
          $treaty_articles_data = self::getRequestData($url);


        }

      }
    }


    // Prepare templates.
//    $docs = $template_data['docs'];
//    $template_data['docs'] = [];
//    foreach($docs as $delta => $doc_item) {
//      $template_data['docs'][] = [
//        '#theme' => 'gpleo-treaty-text-template',
//        '#doc_item' => $doc_item,
//      ];
//    }

    // Prepare output.
    $template_name = 'gpleo_treaty_block_template';

//    $markup = self::customRender($template_name, $template_data);

    $response = [
      'markup' => $markup,
    ];

    return new JsonResponse($response);
  }

  /**
   * Get data from informea server.
   *
   * @return JSON.
   */
  public function getTreatyDecisions(Request $request, TermInterface $taxonomy_term) {
    $content_type = 'decision';
    $fields = self::getDefaultQueryFields();

    $template_data = self::prepareData($taxonomy_term, $fields, $content_type);
    // Prepare templates.
    $docs = $template_data['docs'];
    $template_data['docs'] = [];
    foreach($docs as $delta => $doc_item) {
      $template_data['docs'][] = [
        '#theme' => 'gpleo-decision-template',
        '#doc_item' => $doc_item,
      ];
    }

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
   *
   * @return JSON.
   */
  public function getDocumentsAndLiterature(Request $request, TermInterface $taxonomy_term) {
    $content_type = 'document OR literature';
    $fields = self::getDefaultQueryFields();
    $informea_tid = $taxonomy_term->get('field_informea_tid')->getString();
    $limit = 5;
    $page = \Drupal::request()->query->get('page');
    if (!$page || intval($page) <= 0) {
      $page = 1;
    }

    $url = self::buildQuery($content_type, $informea_tid, $fields, $page, $limit);
    $template_data = self::getRequestData($url);

    // Prepare templates.
    $docs = $template_data['docs'];
    $template_data['docs'] = [];
    foreach($docs as $delta => $doc_item) {
      $template_data['docs'][] = [
        '#theme' => 'gpleo-document-template',
        '#doc_item' => $doc_item,
      ];
    }

    $template_name = 'gpleo_document_block_template';

    // May be better return JSON without render.
    $markup = self::customRender($template_name, $template_data);

    $response = [
      '#markup' => $markup,
    ];

    return new JsonResponse($response);
  }

  /**
   * Get data from informea server.
   *
   * @return JSON.
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
    $data = self::getRequestData($url);

    $template_name = 'gpleo_goal_block_template';

    // May be better return JSON without render.
    $markup = self::customRender($template_name, $data);

    $response = [
      '#markup' => $markup,
    ];

    return new JsonResponse($response);
  }

}
