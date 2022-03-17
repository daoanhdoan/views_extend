<?php

namespace Drupal\views_extend\Plugin\views\area;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url as CoreUrl;
use Drupal\views\Plugin\views\area\TokenizeAreaPluginBase;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Render\ViewsRenderPipelineMarkup;

/**
 * Views area text handler.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("views_extend_text")
 */
class ViewsExtendText extends TokenizeAreaPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['content'] = ['default' => ''];
    $options['alter'] = ['default' => array(
      'make_link' => array('default' => ''),
      'path' => array('default' => ''),
      'absolute' => array('default' => FALSE),
      'external' => array('default' => FALSE),
      'replace_spaces' => array('default' => FALSE),
      'path_case' => array('default' => 'none'),
      'trim_whitespace' => array('default' => FALSE),
      'alt' => array('default' => ''),
      'rel' => array('default' => ''),
      'link_class' => array('default' => ''),
      'prefix' => array('default' => ''),
      'suffix' => array('default' => ''),
      'target' => array('default' => ''),
      'destinations' => array('default' => FALSE),
    )];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['content'] = [
      '#title' => $this->t('Content'),
      '#type' => 'textarea',
      '#default_value' => $this->options['content'],
      '#rows' => 6,
    ];

    $form['alter'] = [
      '#title' => $this->t('Rewrite results'),
      '#type' => 'details',
      '#weight' => 100,
    ];

    $form['alter']['make_link'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Output this field as a custom link'),
      '#default_value' => $this->options['alter']['make_link'],
    ];
    $form['alter']['path'] = [
      '#title' => $this->t('Link path'),
      '#type' => 'textfield',
      '#default_value' => $this->options['alter']['path'],
      '#description' => $this->t('The Drupal path or absolute URL for this link. You may enter data from this view as per the "Replacement patterns" below.'),
      '#states' => [
        'visible' => [
          ':input[name="options[alter][make_link]"]' => ['checked' => TRUE],
        ],
      ],
      '#maxlength' => 255,
    ];
    $form['alter']['absolute'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use absolute path'),
      '#default_value' => $this->options['alter']['absolute'],
      '#states' => [
        'visible' => [
          ':input[name="options[alter][make_link]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['alter']['replace_spaces'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Replace spaces with dashes'),
      '#default_value' => $this->options['alter']['replace_spaces'],
      '#states' => [
        'visible' => [
          ':input[name="options[alter][make_link]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['alter']['external'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('External server URL'),
      '#default_value' => $this->options['alter']['external'],
      '#description' => $this->t("Links to an external server using a full URL: e.g. 'http://www.example.com' or 'www.example.com'."),
      '#states' => [
        'visible' => [
          ':input[name="options[alter][make_link]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['alter']['path_case'] = [
      '#type' => 'select',
      '#title' => $this->t('Transform the case'),
      '#description' => $this->t('When printing URL paths, how to transform the case of the filter value.'),
      '#states' => [
        'visible' => [
          ':input[name="options[alter][make_link]"]' => ['checked' => TRUE],
        ],
      ],
      '#options' => [
        'none' => $this->t('No transform'),
        'upper' => $this->t('Upper case'),
        'lower' => $this->t('Lower case'),
        'ucfirst' => $this->t('Capitalize first letter'),
        'ucwords' => $this->t('Capitalize each word'),
      ],
      '#default_value' => $this->options['alter']['path_case'],
    ];
    $form['alter']['link_class'] = [
      '#title' => $this->t('Link class'),
      '#type' => 'textfield',
      '#default_value' => $this->options['alter']['link_class'],
      '#description' => $this->t('The CSS class to apply to the link.'),
      '#states' => [
        'visible' => [
          ':input[name="options[alter][make_link]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['alter']['alt'] = [
      '#title' => $this->t('Title text'),
      '#type' => 'textfield',
      '#default_value' => $this->options['alter']['alt'],
      '#description' => $this->t('Text to place as "title" text which most browsers display as a tooltip when hovering over the link.'),
      '#states' => [
        'visible' => [
          ':input[name="options[alter][make_link]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['alter']['rel'] = [
      '#title' => $this->t('Rel Text'),
      '#type' => 'textfield',
      '#default_value' => $this->options['alter']['rel'],
      '#description' => $this->t('Include Rel attribute for use in lightbox2 or other javascript utility.'),
      '#states' => [
        'visible' => [
          ':input[name="options[alter][make_link]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['alter']['prefix'] = [
      '#title' => $this->t('Prefix text'),
      '#type' => 'textfield',
      '#default_value' => $this->options['alter']['prefix'],
      '#description' => $this->t('Any text to display before this link. You may include HTML.'),
      '#states' => [
        'visible' => [
          ':input[name="options[alter][make_link]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['alter']['suffix'] = [
      '#title' => $this->t('Suffix text'),
      '#type' => 'textfield',
      '#default_value' => $this->options['alter']['suffix'],
      '#description' => $this->t('Any text to display after this link. You may include HTML.'),
      '#states' => [
        'visible' => [
          ':input[name="options[alter][make_link]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['alter']['target'] = [
      '#title' => $this->t('Target'),
      '#type' => 'textfield',
      '#default_value' => $this->options['alter']['target'],
      '#description' => $this->t("Target of the link, such as _blank, _parent or an iframe's name. This field is rarely used."),
      '#states' => [
        'visible' => [
          ':input[name="options[alter][make_link]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    // Setup the tokens for fields.
    $previous = $this->getPreviousFieldLabels();
    $optgroup_arguments = (string) t('Arguments');
    $optgroup_fields = (string) t('Fields');
    foreach ($previous as $id => $label) {
      $options[$optgroup_fields]["{{ $id }}"] = substr(strrchr($label, ":"), 2);
    }
    // Add the field to the list of options.
    $options[$optgroup_fields]["{{ {$this->options['id']} }}"] = substr(strrchr($this->adminLabel(), ":"), 2);

    foreach ($this->view->display_handler->getHandlers('argument') as $arg => $handler) {
      $options[$optgroup_arguments]["{{ arguments.$arg }}"] = $this->t('@argument title', ['@argument' => $handler->adminLabel()]);
      $options[$optgroup_arguments]["{{ raw_arguments.$arg }}"] = $this->t('@argument input', ['@argument' => $handler->adminLabel()]);
    }

    $this->documentSelfTokens($options[$optgroup_fields]);

    // Default text.

    $output = [];
    $output[] = [
      '#markup' => '<p>' . $this->t('You must add some additional fields to this display before using this field. These fields may be marked as <em>Exclude from display</em> if you prefer. Note that due to rendering order, you cannot use fields that come after this field; if you need a field not listed here, rearrange your fields.') . '</p>',
    ];
    // We have some options, so make a list.
    if (!empty($options)) {
      $output[] = [
        '#markup' => '<p>' . $this->t("The following replacement tokens are available for this field. Note that due to rendering order, you cannot use fields that come after this field; if you need a field not listed here, rearrange your fields.") . '</p>',
      ];
      foreach (array_keys($options) as $type) {
        if (!empty($options[$type])) {
          $items = [];
          foreach ($options[$type] as $key => $value) {
            $items[] = $key . ' == ' . $value;
          }
          $item_list = [
            '#theme' => 'item_list',
            '#items' => $items,
          ];
          $output[] = $item_list;
        }
      }
    }
    // This construct uses 'hidden' and not markup because process doesn't
    // run. It also has an extra div because the dependency wants to hide
    // the parent in situations like this, so we need a second div to
    // make this work.
    $form['alter']['help'] = [
      '#type' => 'details',
      '#title' => $this->t('Replacement patterns'),
      '#value' => $output,
      '#states' => [
        'visible' => [
          [
            ':input[name="options[alter][make_link]"]' => ['checked' => TRUE],
          ],
          [
            ':input[name="options[alter][alter_text]"]' => ['checked' => TRUE],
          ],
          [
            ':input[name="options[alter][more_link]"]' => ['checked' => TRUE],
          ],
        ],
      ],
    ];
  }

  /**
   * Returns all field labels of fields before this field.
   *
   * @return array
   *   An array of field labels keyed by their field IDs.
   */
  protected function getPreviousFieldLabels() {
    $all_fields = $this->view->display_handler->getFieldLabels();
    $field_options = array_slice($all_fields, 0, array_search($this->options['id'], array_keys($all_fields)));
    return $field_options;
  }

  /**
   * Document any special tokens this field might use for itself.
   *
   * @see addSelfTokens()
   */
  protected function documentSelfTokens(&$tokens) {}

  /**
   * Render this field as a link, with the info from a fieldset set by
   * the user.
   */
  protected function renderAsLink($alter, $text, $tokens) {
    $options = [
      'absolute' => !empty($alter['absolute']) ? TRUE : FALSE,
      'alias' => FALSE,
      'entity' => NULL,
      'entity_type' => NULL,
      'fragment' => NULL,
      'language' => NULL,
      'query' => [],
    ];

    $alter += [
      'path' => NULL,
    ];

    $path = $alter['path'];
    // strip_tags() and viewsTokenReplace remove <front>, so check whether it's
    // different to front.
    if ($path != '<front>') {
      // Use strip_tags as there should never be HTML in the path.
      // However, we need to preserve special characters like " that were
      // removed by Html::escape().
      $path = Html::decodeEntities($this->viewsTokenReplace($alter['path'], $tokens));

      // Tokens might contain <front>, so check for <front> again.
      if ($path != '<front>') {
        $path = strip_tags($path);
      }

      // Tokens might have resolved URL's, as is the case for tokens provided by
      // Link fields, so all internal paths will be prefixed by base_path(). For
      // proper further handling reset this to internal:/.
      if (strpos($path, base_path()) === 0) {
        $path = 'internal:/' . substr($path, strlen(base_path()));
      }

      // If we have no $path and no $alter['url'], we have nothing to work with,
      // so we just return the text.
      if (empty($path) && empty($alter['url'])) {
        return $text;
      }

      // If no scheme is provided in the $path, assign the default 'http://'.
      // This allows a url of 'www.example.com' to be converted to
      // 'http://www.example.com'.
      // Only do this when flag for external has been set, $path doesn't contain
      // a scheme and $path doesn't have a leading /.
      if ($alter['external'] && !parse_url($path, PHP_URL_SCHEME) && strpos($path, '/') !== 0) {
        // There is no scheme, add the default 'http://' to the $path.
        $path = "http://" . $path;
      }
    }

    if (empty($alter['url'])) {
      if (!parse_url($path, PHP_URL_SCHEME)) {
        // @todo Views should expect and store a leading /. See
        //   https://www.drupal.org/node/2423913.
        $alter['url'] = CoreUrl::fromUserInput('/' . ltrim($path, '/'));
      }
      else {
        $alter['url'] = CoreUrl::fromUri($path);
      }
    }

    $options = $alter['url']->getOptions() + $options;

    $path = $alter['url']->setOptions($options)->toUriString();

    if (!empty($alter['path_case']) && $alter['path_case'] != 'none' && !$alter['url']->isRouted()) {
      $path = str_replace($alter['path'], $this->caseTransform($alter['path'], $this->options['alter']['path_case']), $path);
    }

    if (!empty($alter['replace_spaces'])) {
      $path = str_replace(' ', '-', $path);
    }

    // Parse the URL and move any query and fragment parameters out of the path.
    $url = UrlHelper::parse($path);

    // Seriously malformed URLs may return FALSE or empty arrays.
    if (empty($url)) {
      return $text;
    }

    // If the path is empty do not build a link around the given text and return
    // it as is.
    // http://www.example.com URLs will not have a $url['path'], so check host as well.
    if (empty($url['path']) && empty($url['host']) && empty($url['fragment']) && empty($url['url'])) {
      return $text;
    }

    // If we get to here we have a path from the url parsing. So assign that to
    // $path now so we don't get query strings or fragments in the path.
    $path = $url['path'];

    if (isset($url['query'])) {
      // Remove query parameters that were assigned a query string replacement
      // token for which there is no value available.
      foreach ($url['query'] as $param => $val) {
        if ($val == '%' . $param) {
          unset($url['query'][$param]);
        }
        // Replace any empty query params from URL parsing with NULL. So the
        // query will get built correctly with only the param key.
        // @see \Drupal\Component\Utility\UrlHelper::buildQuery().
        if ($val === '') {
          $url['query'][$param] = NULL;
        }
      }

      $options['query'] = $url['query'];
    }

    if (isset($url['fragment'])) {
      $path = strtr($path, ['#' . $url['fragment'] => '']);
      // If the path is empty we want to have a fragment for the current site.
      if ($path == '') {
        $options['external'] = TRUE;
      }
      $options['fragment'] = $url['fragment'];
    }

    $alt = $this->viewsTokenReplace($alter['alt'], $tokens);
    // Set the title attribute of the link only if it improves accessibility
    if ($alt && $alt != $text) {
      $options['attributes']['title'] = Html::decodeEntities($alt);
    }

    $class = $this->viewsTokenReplace($alter['link_class'], $tokens);
    if ($class) {
      $options['attributes']['class'] = [$class];
    }

    if (!empty($alter['rel']) && $rel = $this->viewsTokenReplace($alter['rel'], $tokens)) {
      $options['attributes']['rel'] = $rel;
    }

    $target = trim($this->viewsTokenReplace($alter['target'], $tokens));
    if (!empty($target)) {
      $options['attributes']['target'] = $target;
    }

    // Allow the addition of arbitrary attributes to links. Additional attributes
    // currently can only be altered in preprocessors and not within the UI.
    if (isset($alter['link_attributes']) && is_array($alter['link_attributes'])) {
      foreach ($alter['link_attributes'] as $key => $attribute) {
        if (!isset($options['attributes'][$key])) {
          $options['attributes'][$key] = $this->viewsTokenReplace($attribute, $tokens);
        }
      }
    }

    // If the query and fragment were programmatically assigned overwrite any
    // parsed values.
    if (isset($alter['query'])) {
      // Convert the query to a string, perform token replacement, and then
      // convert back to an array form for
      // \Drupal\Core\Utility\LinkGeneratorInterface::generate().
      $options['query'] = UrlHelper::buildQuery($alter['query']);
      $options['query'] = $this->viewsTokenReplace($options['query'], $tokens);
      $query = [];
      parse_str($options['query'], $query);
      $options['query'] = $query;
    }
    if (isset($alter['alias'])) {
      // Alias is a boolean field, so no token.
      $options['alias'] = $alter['alias'];
    }
    if (isset($alter['fragment'])) {
      $options['fragment'] = $this->viewsTokenReplace($alter['fragment'], $tokens);
    }
    if (isset($alter['language'])) {
      $options['language'] = $alter['language'];
    }

    // If the url came from entity_uri(), pass along the required options.
    if (isset($alter['entity'])) {
      $options['entity'] = $alter['entity'];
    }
    if (isset($alter['entity_type'])) {
      $options['entity_type'] = $alter['entity_type'];
    }

    // The path has been heavily processed above, so it should be used as-is.
    $final_url = CoreUrl::fromUri($path, $options);

    // Build the link based on our altered Url object, adding on the optional
    // prefix and suffix
    $render = [
      '#type' => 'link',
      '#title' => $text,
      '#url' => $final_url,
    ];

    if (!empty($alter['prefix'])) {
      $render['#prefix'] = $this->viewsTokenReplace($alter['prefix'], $tokens);
    }
    if (!empty($alter['suffix'])) {
      $render['#suffix'] = $this->viewsTokenReplace($alter['suffix'], $tokens);
    }
    return $this->getRenderer()->render($render);

  }

  /**
   * {@inheritdoc}
   */
  public function getRenderTokens($item) {
    $tokens = [];
    if (!empty($this->view->build_info['substitutions'])) {
      $tokens = $this->view->build_info['substitutions'];
    }
    $count = 0;
    foreach ($this->view->display_handler->getHandlers('argument') as $arg => $handler) {
      $token = "{{ arguments.$arg }}";
      if (!isset($tokens[$token])) {
        $tokens[$token] = '';
      }

      // Use strip tags as there should never be HTML in the path.
      // However, we need to preserve special characters like " that
      // were removed by Html::escape().
      $tokens["{{ raw_arguments.$arg }}"] = isset($this->view->args[$count]) ? strip_tags(Html::decodeEntities($this->view->args[$count])) : '';
      $count++;
    }

    // Get flattened set of tokens for any array depth in query parameters.
    if ($request = $this->view->getRequest()) {
      $tokens += $this->getTokenValuesRecursive($request->query->all());
    }

    // Store the tokens for the row so we can reference them later if necessary.
    $this->last_tokens = $tokens;
    if (!empty($item)) {
      $this->addSelfTokens($tokens, $item);
    }

    return $tokens;
  }

  /**
   * Recursive function to add replacements for nested query string parameters.
   *
   * E.g. if you pass in the following array:
   *   array(
   *     'foo' => array(
   *       'a' => 'value',
   *       'b' => 'value',
   *     ),
   *     'bar' => array(
   *       'a' => 'value',
   *       'b' => array(
   *         'c' => value,
   *       ),
   *     ),
   *   );
   *
   * Would yield the following array of tokens:
   *   array(
   *     '%foo_a' => 'value'
   *     '%foo_b' => 'value'
   *     '%bar_a' => 'value'
   *     '%bar_b_c' => 'value'
   *   );
   *
   * @param $array
   *   An array of values.
   *
   * @param $parent_keys
   *   An array of parent keys. This will represent the array depth.
   *
   * @return
   *   An array of available tokens, with nested keys representative of the array structure.
   */
  protected function getTokenValuesRecursive(array $array, array $parent_keys = []) {
    $tokens = [];

    foreach ($array as $param => $val) {
      if (is_array($val)) {
        // Copy parent_keys array, so we don't affect other elements of this
        // iteration.
        $child_parent_keys = $parent_keys;
        $child_parent_keys[] = $param;
        // Get the child tokens.
        $child_tokens = $this->getTokenValuesRecursive($val, $child_parent_keys);
        // Add them to the current tokens array.
        $tokens += $child_tokens;
      }
      else {
        // Create a token key based on array element structure.
        $token_string = !empty($parent_keys) ? implode('.', $parent_keys) . '.' . $param : $param;
        $tokens['{{ arguments.' . $token_string . ' }}'] = strip_tags(Html::decodeEntities($val));
      }
    }

    return $tokens;
  }

  /**
   * Add any special tokens this field might use for itself.
   *
   * This method is intended to be overridden by items that generate
   * fields as a list. For example, the field that displays all terms
   * on a node might have tokens for the tid and the term.
   *
   * By convention, tokens should follow the format of {{ token__subtoken }}
   * where token is the field ID and subtoken is the field. If the
   * field ID is terms, then the tokens might be {{ terms__tid }} and
   * {{ terms__name }}.
   */
  protected function addSelfTokens(&$tokens, $item) {}

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    if (!$empty || !empty($this->options['alter'])) {
      $alter = $this->options['alter'];
      if (!empty($alter['make_link']) && (!empty($alter['path']) || !empty($alter['url']))) {
        if (!isset($tokens)) {
          $tokens = $this->getRenderTokens($alter);
        }
        return [ '#markup' => $this->renderAsLink($alter, $this->renderTextarea($this->options['content']), $tokens)];
      }
      return [
        '#markup' => $this->renderTextarea($this->options['content']),
      ];
    }

    return [];
  }

  /**
   * Render a text area with \Drupal\Component\Utility\Xss::filterAdmin().
   */
  public function renderTextarea($value) {
    if ($value) {
      return $this->sanitizeValue($this->tokenizeValue($value), 'xss_admin');
    }
  }

}
