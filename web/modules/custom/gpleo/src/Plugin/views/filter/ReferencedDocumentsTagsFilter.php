<?php

namespace Drupal\gpleo\Plugin\views\filter;

use Drupal\views\ViewExecutable;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\Views;


/**
 * Views filter plugin to show only Documents tagged by a specific thesaurus tag in child ECK element.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("gp_referenced_documents_tags")
 */
class ReferencedDocumentsTagsFilter extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
  }

  /**
   * {@inheritdoc}
   */
  protected function operatorForm(&$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function canExpose() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Referenced Documents Tag Filter');
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
   $tid = \Drupal::service('gpleo.terms')->id();
    $definition = [
      'table' => 'node__field_tags',
      'field' => 'entity_id',
      'left_table' => 'node_field_data',
      'left_field' => 'nid',
      'type' => 'INNER',
    ];
    $join = Views::pluginManager('join')->createInstance('standard', $definition);
    $this->query->addRelationship('node__field_tags', $join, 'node__field_tags');

    $definition = [
      'table' => 'child_entity__field_tags',
      'field' => 'entity_id',
      'left_table' => 'node__field_tags',
      'left_field' => 'field_tags_target_id',
      'type' => 'INNER',
    ];
    $join = Views::pluginManager('join')->createInstance('standard', $definition);
    $this->query->addRelationship('child_entity__field_tags', $join, 'child_entity__field_tags');

    if ($tid) {
      $this->query->addWhere('AND', 'child_entity__field_tags.field_tags_target_id', $tid);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();
    return $contexts;
  }

}
