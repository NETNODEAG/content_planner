<?php

namespace Drupal\content_kanban\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Config name
   *
   * @var string
   */
  static $configName = 'content_kanban.settings';

  /**
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * @var \Drupal\content_kanban\KanbanService
   */
  protected $kanbanService;

  public function __construct(\Drupal\Core\Config\ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);

    $this->kanbanService = \Drupal::service('content_kanban.kanban_service');

    //Get config
    $this->config = $this->config(self::$configName);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_kanban_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'content_kanban.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    //If the Content Calendar module is not enabled, set the use color setting to inactive
    if(!$this->kanbanService->contentCalendarModuleIsEnabled()) {
      $this->saveColorSetting(0);
    }

    $config = $this->config(self::$configName);

    $form['options'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Options'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    //Content Calendar colors
    $form['options']['use_content_calendar_colors'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Use the defined colors from Content Calendar'),
      '#description' => $this->t('This setting is only available if the Content Calendar is enabled and configured properly.'),
      '#default_value' => $config->get('use_content_calendar_colors'),
      '#disabled' => !$this->kanbanService->contentCalendarModuleIsEnabled(),
    );

    //Show user thumb checkbox
    $user_picture_field_exists = !$this->config('field.field.user.user.user_picture')->isNew();

    $form['options']['show_user_thumb'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show thumbnail image of User image'),
      '#description' => $this->t('This option is only available, if the User account has the "user_picture" field. See Account configuration.'),
      '#disabled' => !$user_picture_field_exists,
      '#default_value' => $this->config->get('show_user_thumb'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    //Get values
    $values = $form_state->getValues();

    //Get value to use Content Calendar colors
    $use_content_calendar_colors = $values['use_content_calendar_colors'];

    //If Content Calendar module is disabled, then disable usage of colors
    if(!$this->kanbanService->contentCalendarModuleIsEnabled()) {
      $use_content_calendar_colors = 0;
    }

    //Save settings into configuration
    $this->saveColorSetting($use_content_calendar_colors);

    //Save show user image thumbnail option
    $this->config(self::$configName)
      ->set('show_user_thumb', $values['show_user_thumb'])
      ->save();
  }

  /**
   * Save color setting
   *
   * @param $value
   */
  protected function saveColorSetting($value) {
    $this->config(self::$configName)
      ->set('use_content_calendar_colors', $value)
      ->save();
  }


}