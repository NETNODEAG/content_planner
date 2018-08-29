<?php

namespace Drupal\content_planner\Form;

use Drupal\content_planner\DashboardSettingsService;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class DashboardBlockConfigForm extends ConfigFormBase {

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
  public function __construct(\Drupal\Core\Config\ConfigFactoryInterface $config_factory) {

    parent::__construct($config_factory);

    $this->dashboardSettingsService = \Drupal::service('content_planner.dashboard_settings_service');

    $this->dashboardBlockPluginManager = \Drupal::service('content_planner.dashboard_block_plugin_manager');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_planner_dashboard_block_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      DashboardSettingsService::$configName
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL, $block_id = NULL) {

    //Get plugin definition
    $plugin_definition = $this->dashboardBlockPluginManager->getDefinition($block_id);

    if(!$plugin_definition) {
      $this->messenger()->addError($this->t('Plugin with @block_id does not exist', array('@block_id' => $block_id)));
      return array();
    }

    $form['block_id'] = array(
      '#type' => 'value',
      '#value' => $block_id,
    );

    //Get block configuration
    $block_configuration = $this->dashboardSettingsService->getBlockConfiguration($block_id);

    //Build Basic Fields
    $this->buildBasicFields($form, $form_state, $request, $block_configuration);

    //Create instance
    $instance = $this->createInstanceFromBlockID($block_id);

    //Build plugin form elements
    if($config_specific_fields = $instance->getConfigSpecificFormFields($form_state, $request, $block_configuration)) {

      $form['plugin_specific_config'] = [
        '#type' => 'fieldset',
        '#title' => t('Extra Settings'),
        '#collapsible' => FALSE,
        '#collapsed' => FALSE,
        '#tree' => TRUE,
      ];

      foreach($config_specific_fields as $field_name => $field_settings) {

        $form['plugin_specific_config'][$field_name] = $field_settings;
      }

    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * Build Basic fields which are the same for every Dashboard Block Plugin
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param $block_configuration
   */
  protected function buildBasicFields(array &$form, FormStateInterface &$form_state, Request &$request, $block_configuration) {

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 255,
      '#default_value' => (isset($block_configuration['title'])) ? $block_configuration['title'] : '',
      '#description' => $this->t("If this field is blank, the Plugin's default name will be used."),
    ];

    $form['weight'] = array(
      '#type' => 'weight',
      '#title' => $this->t('Weight'),
      '#default_value' => (isset($block_configuration['weight'])) ? $block_configuration['weight'] : 0,
      '#delta' => 10,
    );

  }

  /**
   * @param string $block_id
   *
   * @return \Drupal\content_planner\DashboardBlockInterface
   */
  protected function createInstanceFromBlockID($block_id) {
    $plugin_definition = $this->dashboardBlockPluginManager->getDefinition($block_id);

    return $this->dashboardBlockPluginManager->createInstance($plugin_definition['id']);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    //Validate form by parent
    parent::validateForm($form, $form_state);

    //Get block id from from state
    $block_id = $form_state->getValue('block_id');

    //Create instance
    $instance = $this->createInstanceFromBlockID($block_id);

    //validate form by plugin
    $instance->validateForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    //Get block id from from state
    $block_id = $form_state->getValue('block_id');


    //Get specific block configuration
    $block_configuration = $this->dashboardSettingsService->getBlockConfiguration($block_id);

    //Set fields
    $block_configuration['title'] = $form_state->getValue('title');
    $block_configuration['weight'] = $form_state->getValue('weight');
    $block_configuration['configured'] = TRUE;
    $block_configuration['plugin_specific_config'] = $form_state->getValue('plugin_specific_config');

    //Save configurations back into config
    $this->dashboardSettingsService->saveBlockConfiguration($block_id, $block_configuration);
  }


}