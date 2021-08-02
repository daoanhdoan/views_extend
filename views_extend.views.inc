<?php
/**
 *
 */
/**
 * Implements @see hook_views_data_alter().
 */
function views_extend_views_data_alter(&$data) {
  $data['views']['views_extend_result'] = [
    'title' => t('Views Extend: Result'),
    'area' => [
      'id' => 'views_extend_result',
    ],
  ];
  $data['views']['views_extend_text'] = [
    'title' => t('Views Extend: Text'),
    'area' => [
      'id' => 'views_extend_text',
    ],
  ];
  $data['views']['pager'] = array(
    'title' => t('Pager'),
    'help' => t('Provide pager for this view.'),
    'area' => array(
      'id' => 'views_extend_pager',
    ),
  );

}
