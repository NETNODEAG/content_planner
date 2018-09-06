<?php

namespace Drupal\content_planner\Form;

use Drupal\content_planner\DashboardBlockBase;
use Drupal\content_planner\DashboardSettingsService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class DashboardSettingsForm extends ConfigFormBase {

  /**
   * @var \Drupal\content_planner\DashboardSettingsService
   */
  protected $dashboardSettingsService;

  /**
   * @var \Drupal\content_planner\DashboardBlockPluginManager
   */
  protected $dashboardBlockPluginManager;

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory) {

    parent::__construct($config_factory);

    //Get config
    $this->dashboardSettingsService = \Drupal::service('content_planner.dashboard_settings_service');
    $this->dashboardBlockPluginManager = \Drupal::service('content_planner.dashboard_block_plugin_manager');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_planner_dashboard_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      DashboardSettingsService::$configName,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    //Get block configurations from settings
    $block_configurations = $this->dashboardSettingsService->getBlockConfigurations();

    //Get registered Plugins
    $plugins = $this->dashboardBlockPluginManager->getDefinitions();
    uksort($plugins, function($a, $b) {return strnatcmp($a, $b);});

    $block_options = array();

    //Loop over every plugin and generate renderable array
    foreach ($plugins as $plugin_id => $plugin) {

      $block_options[$plugin_id] = $plugin['name'];

      if(array_key_exists($plugin_id, $block_configurations)) {

        $config_link = Link::createFromRoute(
          $this->t('Configure'),
          'content_planner.dashboard_block_config_form',
          array('block_id' => $plugin_id),
          array('query' => array(
            'destination' => Url::fromRoute('content_planner.dashboard_settings')->toString()
          ))
        );

        $block_options[$plugin_id] .= ' (' . $config_link->toString() . ')';
      }
    }

    //default value
    if($block_configurations) {
      $default_value = array_keys($block_configurations);
    } else {
      $default_value = array();
    }

    $form['available_dashboard_blocks'] = [
      '#type' => 'fieldset',
      '#title' => t('Dashboard Widgets'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    $form['available_dashboard_blocks']['enabled_blocks'] = [
      '#type' => 'checkboxes',
      '#title' => t('Available Dashboard Widgets'),
      '#description' => t('Select which Widgets should be displayed in the Dashboard.'),
      //'#required' => TRUE,
      '#options' => $block_options,
      '#default_value' => $default_value,
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $block_configurations = $this->dashboardSettingsService->getBlockConfigurations();

    //Get values
    $values = $form_state->getValues();

    foreach($values['enabled_blocks'] as $key => $selected) {

      if($selected) {

        //If the block is not in the config already
        if(!array_key_exists($key, $block_configurations)) {
          $config = DashboardBlockBase::getBasicConfigStructure();

          $config['plugin_id'] = $key;
          $block_configurations[$key] = $config;
        }


      } else {

        //Delete block if deselecte
        if(array_key_exists($key, $block_configurations)) {
          unset($block_configurations[$key]);
        }

      }
    }

    //Save
    $this->dashboardSettingsService->saveBlockConfigurations($block_configurations);
  }


}