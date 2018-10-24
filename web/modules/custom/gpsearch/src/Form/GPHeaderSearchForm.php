<?php
/**
 * @file
 * Contains \Drupal\gpsearch\Form\SearchFiltersForm.
 */

// @TODO Remove it if all ok.
namespace Drupal\gpsearch\Form;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;


class GPHeaderSearchForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gpw_search_filters';
  }


  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param null $title
   *  The title of the block.
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state, $title = NULL) {
    $form = [
      '#theme' => 'gpsearch_block_template',
      'panel' => [
        '#attributes' => ['class' => ['search-filters', 'invisible']],
        '#title' => $title,
        '#type' => 'fieldset',
      ],
    ];
    if ($q = self::getQueryString()) {
      $form['q'] = ['#type' => 'hidden', '#value' => $q];
    }
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#attributes' => ['class' => ['search-submit']],
      '#type' => 'submit',
      '#value' => $this->t('Search')
    ];
    $form['actions']['reset'] = [
      '#attributes' => [
        'class' => ['btn', 'btn-default', 'btn-sm', 'search-reset'],
        'href' => '#',
      ],
      '#tag' => 'a',
      '#type' => 'html_tag',
      '#value' => $this->t('Reset all filters')
    ];
    $form['#method'] = 'get';
    return $form;
  }

  public static function getQueryString() {
    if (!empty($_GET['q'])) {
      $q = trim($_GET['q']);
      $q = Html::escape($q);
    }
    return !empty($q) ? $q : '';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //This function has to be empty because we use $form['#method'] = 'get'
  }
}
