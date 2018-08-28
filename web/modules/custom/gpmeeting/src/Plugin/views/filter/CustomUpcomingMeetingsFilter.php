<?php

namespace Drupal\gpmeeting\Plugin\views\filter;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Filters nodes on current domain_id
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("upcoming_meetings_filter")
 */
class CustomUpcomingMeetingsFilter extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Custom Upcoming Meetings Filter');
  }

  public function query() {

    $nu_in_utc = new \DateTime('now', new \DateTimezone('UTC'));
    $nu_date = $nu_in_utc->format('Y-m-d');

    $configuration = [
      'table' => 'node__field_date_range',
      'field' => 'entity_id',
      'left_table' => 'node_field_data',
      'left_field' => 'nid',
      'type' => 'LEFT',
      'operator' => 'AND'
    ];
    try {
      $join = Views::pluginManager('join')->createInstance('standard', $configuration);
    } catch (PluginException $exception) {
      // Do nothing.
    }


    $this->query->addRelationship('node__field_date_range', $join, 'node_field_data');
    $this->query->addWhere('AND', 'node__field_date_range.field_date_range_value', $nu_date, '>=');
  }

}