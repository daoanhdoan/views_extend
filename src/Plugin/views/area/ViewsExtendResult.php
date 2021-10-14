<?php

namespace Drupal\views_extend\Plugin\views\area;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url as UrlObject;
use Drupal\views\Plugin\views\area\AreaPluginBase;
use Drupal\views\Plugin\views\style\DefaultSummary;

/**
 * Views area handler to display some configurable result summary.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("views_extend_result")
 */
class ViewsExtendResult extends AreaPluginBase
{

  /**
   * {@inheritdoc}
   */
  protected function defineOptions()
  {
    $options = parent::defineOptions();

    $options['content'] = [
      'default' => $this->t('Displaying @start - @end of @total, @items_per_page rows per page. @pager'),
    ];
    $options['hide_default_pager'] = [
      'default' => TRUE,
    ];
    $options['element_type'] = ['default' => ''];
    $options['element_class'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state)
  {
    parent::buildOptionsForm($form, $form_state);
    $item_list = [
      '#theme' => 'item_list',
      '#items' => [
        '@start -- the initial record number in the set',
        '@end -- the last record number in the set',
        '@total -- the total records in the set',
        '@label -- the human-readable name of the view',
        '@per_page -- the number of items per page',
        '@items_per_page -- the number of rows per page',
        '@current_page -- the current page number',
        '@current_record_count -- the current page record count',
        '@page_count -- the total page count',
        '@pager -- the pager'
      ],
    ];
    $list = \Drupal::service('renderer')->render($item_list);
    $form['content'] = [
      '#title' => $this->t('Display'),
      '#type' => 'textarea',
      '#rows' => 3,
      '#default_value' => $this->options['content'],
      '#description' => $this->t('You may use HTML code in this field. The following tokens are supported:') . $list,
    ];
    $form['hide_default_pager'] = [
      '#title' => $this->t('Hide default pager'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['hide_default_pager'],
      '#description' => $this->t('Hide default pager by view'),
    ];

    $form['element_type'] = [
      '#title' => $this->t('HTML element'),
      '#options' => $this->getElements(),
      '#type' => 'select',
      '#default_value' => $this->options['element_type'],
      '#description' => $this->t('Choose the HTML element to wrap around this field, e.g. H1, H2, etc.'),
    ];
    $form['element_class'] = [
      '#title' => $this->t('CSS class'),
      '#description' => $this->t('You may use token substitutions from the rewriting section in this class.'),
      '#type' => 'textfield',
      '#default_value' => $this->options['element_class'],
    ];
  }

  public function getElements() {
    static $elements = NULL;
    if (!isset($elements)) {
      // @todo Add possible html5 elements.
      $elements = [
        '' => $this->t('- Use default -'),
        '0' => $this->t('- None -'),
      ];
      $elements += \Drupal::config('views.settings')->get('field_rewrite_elements');
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function query()
  {
    if (strpos($this->options['content'], '@total') !== FALSE) {
      $this->view->get_total_rows = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE)
  {
    // Must have options and does not work on summaries.
    if (!isset($this->options['content']) || $this->view->style_plugin instanceof DefaultSummary) {
      return [];
    }
    $output = '';
    $format = $this->options['content'];
    // Calculate the page totals.
    $current_page = (int)$this->view->getCurrentPage() + 1;
    $per_page = !empty($_GET['items_per_page']) ? $_GET['items_per_page'] : (int)$this->view->getItemsPerPage();
    if ($per_page == 'All') {
      $per_page = 0;
    }
    // @TODO: Maybe use a possible is views empty functionality.
    // Not every view has total_rows set, use view->result instead.
    $total = isset($this->view->total_rows) ? $this->view->total_rows : count($this->view->result);
    $label = Html::escape($this->view->storage->label());
    // If there is no result the "start" and "current_record_count" should be
    // equal to 0. To have the same calculation logic, we use a "start offset"
    // to handle all the cases.
    $start_offset = empty($total) ? 0 : 1;
    if ($per_page === 0) {
      $page_count = 1;
      $start = $start_offset;
      $end = $total;
    } else {
      $page_count = (int)ceil($total / $per_page);
      $total_count = $current_page * $per_page;
      if ($total_count > $total) {
        $total_count = $total;
      }
      $start = ($current_page - 1) * $per_page + $start_offset;
      $end = $total_count;
    }
    $current_record_count = ($end - $start) + $start_offset;

    $pager = $this->view->display_handler->getPlugin('pager');
    if ($pager->getPluginId() == 'full' || $pager->getPluginId() == 'mini') {
      $items = views_extend_items_per_page_links($this->view);

      $pager_element = [
        '#type' => 'container',
        '#attributes' => array('class' => array('views-extend-pager clearfix')),
      ];

      $pager_element['items'] = array(
        '#type' => 'dropbutton',
        '#dropbutton_type' => 'small',
        '#links' => $items,
        '#attributes' => array('class' => array('views-extend-pager-dropbutton'))
      );

      if (!empty($pager->options['expose']['prefix'])) {
        $pager_element['items']['#prefix'] = $pager->options['expose']['prefix'];
      }
      if (!empty($pager->options['expose']['suffix'])) {
        $pager_element['items']['#suffix'] = $pager->options['expose']['suffix'];
      }

      $pager_element['#attached']['library'][] = 'views_extend/views_extend';
      $pager_element = render($pager_element);
    } else {
      $pager_element = "";
    }

    // Get the search information.
    $replacements = [];
    $replacements['@start'] = $start;
    $replacements['@end'] = $end;
    $replacements['@total'] = $total;
    $replacements['@label'] = $label;
    $replacements['@per_page'] = $per_page;
    $replacements['@current_page'] = $current_page;
    $replacements['@current_record_count'] = $current_record_count;
    $replacements['@page_count'] = $page_count;
    $replacements['@items_per_page'] = $pager_element;
    $replacements['@pager'] = "";

    $pager = $this->view->display_handler->getPlugin('pager');
    if ($pager->getPluginId() == 'full' || $pager->getPluginId() == 'mini') {
      $tags = [
        0 => !empty($pager->options['tags']['first']) ? $pager->options['tags']['first'] : NULL,
        1 => !empty($pager->options['tags']['previous']) ? $pager->options['tags']['previous'] : NULL,
        3 => !empty($pager->options['tags']['next']) ? $pager->options['tags']['next'] : NULL,
        4 => !empty($pager->options['tags']['last']) ? $pager->options['tags']['last'] : NULL,
      ];
      $input = isset($this->view->exposed_raw_input) ? $this->view->exposed_raw_input : NULL;
      $pager = [
        '#theme' => $pager->themeFunctions(),
        '#tags' => $tags,
        '#element' => $pager->options['id'],
        '#parameters' => $input,
        '#quantity' => !empty($pager->options['quantity']) ? $pager->options['quantity'] : 0,
        '#route_name' => !empty($pager->view->live_preview) ? '<current>' : '<none>',
      ];
      $replacements['@pager'] = render($pager);
    }

    // Send the output.
    if (!empty($total) || !empty($this->options['empty'])) {
      $output .= Xss::filterAdmin(str_replace(array_keys($replacements), array_values($replacements), $format));
      // Return as render array.

      $el = ($this->options['element_type'] === '') ? 'span' : (($this->options['element_type'] === 0) ? "" : $this->options['element_type']);

      if ($el) {
        return [
          '#markup' => '<' . $el . ' class="view-result ' . $this->options['element_class'] . '">' . $output . '</' . $el . '>',
        ];
      }
      else {
        return [
          '#markup' =>  $output
        ];
      }
    }

    return [];
  }

}
