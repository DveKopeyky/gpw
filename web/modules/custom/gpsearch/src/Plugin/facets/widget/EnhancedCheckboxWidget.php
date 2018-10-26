<?php

namespace Drupal\gpsearch\Plugin\facets\widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\widget\CheckboxWidget;

/**
 * List of checkbox presented in UI overlay with drop-down.
 *
 * @FacetsWidget(
 *   id = "enhanced_checkboxes",
 *   label = @Translation("Enhanced list of checkboxes"),
 *   description = @Translation("A configurable widget that shows a list of checkboxes in overlay"),
 * )
 */
class EnhancedCheckboxWidget extends CheckboxWidget {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = [
        'default_option_label' => 'Choose',
        'subtitle' => '',
        'collapsed' => FALSE,
      ] + parent::defaultConfiguration();
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $build = parent::build($facet);
    $build['#attached']['library'][] = 'gpsearch/drupal.gpsearch.enhanced-checkbox-widget';
    foreach($build['#items'] as &$item) {
      $item['#title']['#theme'] = 'facets_result_item_enhanced_select';
    }
    return $build;
  }


  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $config = $this->getConfiguration();

    $message = $this->t('To achieve the standard behavior of a dropdown, you need to enable the facet setting below <em>"Ensure that only one result can be displayed"</em>.');
    $form['warning'] = [
      '#markup' => '<div class="messages messages--warning">' . $message . '</div>',
    ];

    $form += parent::buildConfigurationForm($form, $form_state, $facet);

    $form['default_option_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default option label'),
      '#default_value' => $config['default_option_label'],
    ];

    $form['subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subtitle'),
      '#default_value' => $config['subtitle'],
    ];

    $form['collapsed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Collapse facet items by default'),
      '#default_value' => $config['collapsed'],
    ];

    return $form;
  }
}
