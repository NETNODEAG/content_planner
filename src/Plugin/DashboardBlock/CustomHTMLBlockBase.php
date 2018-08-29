<?php

/**
 * @file
 * Contains \Drupal\content_planner\Plugin\DashboardBlock\UserBlock.
 */

namespace Drupal\content_planner\Plugin\DashboardBlock;

use Drupal\content_planner\DashboardBlockBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class CustomHTMLBlockBase extends DashboardBlockBase {

  public function build() {

    $build = array();

    //Get config
    $config = $this->getConfiguration();

    //
    if(isset($config['plugin_specific_config']['content'])) {

      $build = array(
        '#markup' => check_markup($config['plugin_specific_config']['content']['value'], $config['plugin_specific_config']['content']['format']),
      );

    }

    return $build;
  }


  /**
   * {@inheritdoc}
   */
  public function getConfigSpecificFormFields(FormStateInterface &$form_state, Request &$request, array $block_configuration) {

    $form = array();

    $default_value = $block_configuration['plugin_specific_config']['content']['value'];

    $form['content'] = array(
      '#type' => 'text_format',
      '#title' => t('Content'),
      '#format'=> 'full_html',
      '#default_value' => $default_value,
    );

    return $form;
  }

}