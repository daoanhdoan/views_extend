<?php
/**
 * @file
 */
/**
 * Implements @see hook_token_info_alter().
 */
function views_extend_token_info_alter(&$info) {
  $info['tokens']['view']['embed'] = array(
    'name' => t('Embed'),
    'description' => t('Embed the view.')
  );
}

/**
 * Implements @see hook_tokens().
 */
function views_extend_tokens($type, $tokens, array $data = array(), array $options = array()) {
  $token_service = \Drupal::token();
  $replacements = array();

  if ($type == 'view') {
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'embed':
          //$replacements[$original] = $sanitize ? check_plain($view->human_name) : $view->human_name;
          break;
      }
    }

    // [view:url:*] nested tokens. This only works if Token module is installed.
    if ($embed_tokens = $token_service->findWithPrefix($tokens, 'embed')) {
      foreach ($embed_tokens as $name => $original) {
        list($view_name, $display_id, $args) = explode(":", $name . str_repeat(":",  2));
        if (!empty($view_name) && !is_numeric($view_name)) {

          $view = $this->viewStorage->load($view_name)->getExecutable();

          if (empty($view) || !$view->access($display_id)) {
            $replacements[$original] = '';
          }
          $view->setDisplay($display_id);

          // Avoid recursion
          $view->parent_views += $this->view->parent_views;
          $view->parent_views[] = "$view_name:$display_id";

          // Check if the view is part of the parent views of this view
          $search = "$view_name:$display_id";
          if (in_array($search, $this->view->parent_views)) {
            $replacements[$original] = t("Recursion detected in view @view display @display.", ['@view' => $view_name, '@display' => $display_id]);
          }
          else {
            if (!empty($this->options['inherit_arguments']) && !empty($this->view->args)) {
              $output = $view->preview($display_id, $this->view->args);
            }
            else {
              $output = $view->preview($display_id);
            }
            $this->isEmpty = $view->display_handler->outputIsEmpty();
            $replacements[$original] = $output;
          }
        }
      }
    }
  }

  return $replacements;
}
