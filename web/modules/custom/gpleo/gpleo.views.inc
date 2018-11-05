<?php

/**
 * Implements hook_views_data_alter.
 */
function gpleo_views_data_alter(array &$data) {
  $data['node_field_data']['gp_referenced_documents_tags'] = [
    'title' => t('Referenced Documents Tag Filter'),
    'help' => t('Views filter plugin to show only Documents tagged by a specific thesaurus tag in child ECK element'),
    'filter' => [
      'field' => 'tid',
      'id' => 'gp_referenced_documents_tags',
    ],
  ];
}
