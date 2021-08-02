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
 * @ViewsArea("views_extend_pager")
 */
class ViewsExtendPager extends AreaPluginBase
{

  /**
   * {@inheritdoc}
   */
  protected function defineOptions()
  {
    $options = parent::defineOptions();
    $options['hide_default_pager'] = [
      'default' => FALSE,
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state)
  {
    parent::buildOptionsForm($form, $form_state);
    $form['hide_default_pager'] = [
      '#title' => $this->t('Hide default pager'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['hide_default_pager'],
      '#description' => $this->t('Hide default pager by view'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE)
  {
    $pager = $this->view->display_handler->getPlugin('pager');
    if ($pager->getPluginId() == 'full' || $pager->getPluginId() == 'mini') {
      $tags = [
        0 => $pager->options['tags']['first'],
        1 => $pager->options['tags']['previous'],
        3 => $pager->options['tags']['next'],
        4 => $pager->options['tags']['last'],
      ];
      $input = isset($this->view->exposed_raw_input) ? $this->view->exposed_raw_input : NULL;
      return [
        '#theme' => $pager->themeFunctions(),
        '#tags' => $tags,
        '#element' => $pager->options['id'],
        '#parameters' => $input,
        '#quantity' => $pager->options['quantity'],
        '#route_name' => !empty($pager->view->live_preview) ? '<current>' : '<none>',
      ];
    }
  }
}
