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
  private function buildQuery($content_type, $informea_tid, $fields = [], $page = 1, $limit = 5) {
    // @TODO replace it with variable.
    $server_url = 'http://informea:solr6@search.informea.org/solr/informea/select';
    $offset = ($page - 1) * $limit;

    // @TODO This code is not work until we can not use fq[].
//    $url_options = [
//      'query' => [
//        'indent' => 'on',
//        'q' => '*:*',
//        'wt' => 'json',
//        'fq' => [
//          'is_status:1',
//          'im_field_informea_tags:' . $informea_tid,
//          'ss_type:' . $content_type,
//        ],
//        'fl' => join(',', $fields),
//        'rows' => $limit,
//        'start' => $offset,
//      ],
//    ];
//
//    $request_link = Url::fromUri($server_url, $url_options)->toString();

    // So I have created this url as simple string.
    $request_link = $server_url . '?'
      . 'indent=on'
      . '&q=*:*'
      . '&wt=json'
      . '&fq=is_status:1'
      . '&fq=ss_type:' . $content_type
      . '&fq=im_field_informea_tags:' . $informea_tid
      . '&fl=' . join(',', $fields)
      . '&rows=' . $limit
      . '&start=' . $offset;

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
   * Get data from informea server.
   *
   * @return JSON.
   */
  public function getTreatyText(Request $request, TermInterface $taxonomy_term) {
    // Get treaty ids. First request.
    $content_type = 'treaty_paragraph OR treaty_article';
    $fields = [
      'im_field_treaty',
    ];
    $informea_tid = $taxonomy_term->get('field_informea_tid')->getString();
    $limit = 5;
    $page = \Drupal::request()->query->get('page');
    if (!$page || intval($page) <= 0) {
      $page = 1;
    }
    $url = self::buildQuery($content_type, $informea_tid, $fields, $page, $limit);
    $data = self::getRequestData($url);

    $docs = $data->response->docs;

    // Prepare output.
    $template_name = 'gpleo_treaty_block_template';

    // May be better return JSON without render.
    $markup = self::customRender($template_name, $data);

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
    $informea_tid = $taxonomy_term->get('field_informea_tid')->getString();
    if ($informea_tid) {
      $limit = 5;
      $page = \Drupal::request()->query->get('page');
      if (!$page || intval($page) <= 0) {
        $page = 1;
      }

      $url = self::buildQuery($content_type, $informea_tid, $fields, $page, $limit);
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
    $data = self::getRequestData($url);

    $template_name = 'gpleo_document_block_template';

    // May be better return JSON without render.
    $markup = self::customRender($template_name, $data);

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
