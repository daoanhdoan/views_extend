<?php
/**
 * @file
 */

/**
 * Create a dropdown content.
 */
function theme_views_extend_ctools_dropdown($vars) {
  // Provide a unique identifier for every dropdown on the page.
  static $id = 0;
  $id++;

  $class = 'ctools-dropdown-no-js ctools-dropdown' . ($vars['class'] ? (' ' . $vars['class']) : '');

  ctools_add_js('dropdown');
  ctools_add_css('dropdown');

  $output = '';

  $output .= '<div class="' . $class . '" id="ctools-dropdown-' . $id . '">';
  $output .= '<div class="ctools-dropdown-link-wrapper">';
  if ($vars['image']) {
    $output .= '<a href="#" class="ctools-dropdown-link ctools-dropdown-image-link">' . $vars['title'] . '</a>';
  }
  else {
    $output .= '<a href="#" class="ctools-dropdown-link ctools-dropdown-text-link">' . $vars['title'] . '</a>';
  }

  $output .= '</div>'; // wrapper
  $output .= '<div class="ctools-dropdown-container-wrapper">';
  $output .= '<div class="ctools-dropdown-container">';
  if (!empty($vars['links'])) {
    $output .= theme_links(array('links' => $vars['links'], 'attributes' => array(), 'heading' => ''));
  }
  if (!empty($vars['content'])) {
    $output .= $vars['content'];
  }
  $output .= '</div>'; // container
  $output .= '</div>'; // container wrapper
  $output .= '</div>'; // dropdown
  return $output;
}

/**
 * Create a dropdown content.
 */
function theme_views_extend_ctools_dropdown_wrapper($vars) {
  $element = $vars['element'];
  $vars += array(
    'content' => $element['#children']
  );
  if (!empty($element['#dropdown'])) {
    $vars += $element['#dropdown'];
  }
  if (empty($vars['title'])) {
    $vars['title'] = $element['#title'];
  }
  unset($vars['element']);
  return theme('views_extend_ctools_dropdown', $vars);
}
