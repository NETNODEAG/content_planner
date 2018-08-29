<?php

namespace Drupal\content_calendar\Form;

use Drupal\content_calendar\Entity\ContentTypeConfig;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\NodeType;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * @var \Drupal\content_calendar\ContentTypeConfigService
   */
  protected $contentTypeConfigService;

  /**
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Config name
   *
   * @var string
   */
  static $configName = 'content_calendar.settings';

  /**
   * The default background color value for unpublished content
   *
   * @var string
   */
  static $defaultBgColorUnpublishedContent = '#fff4f4';

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(\Drupal\Core\Config\ConfigFactoryInterface $config_factory) {

    parent::__construct($config_factory);

    $this->contentTypeConfigService = \Drupal::service('content_calendar.content_type_config_service');

    //Get config
    $this->config = $this->config(self::$configName);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_calendar_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'content_calendar.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    //Get select options for content types
    $content_type_options = $this->getConfiguredContentTypes();

    if(!$content_type_options) {
      $message = $this->t('Content Calendar can only be used with Scheduler. At least one Content Type needs to have the scheduling options enabled.');
      $this->messenger()->addMessage($message, 'error');
      return array();
    }

    //Build Content Type configuration
    $this->buildContentTypeConfiguration($form, $form_state);

    //Build Calendar Options
    $this->buildCalendarOptions($form, $form_state);

    //Build form
    $build_form = parent::buildForm($form, $form_state);

    return $build_form;
  }

  /**
   * Build Content Type select options
   *
   * @return array
   */
  protected function getConfiguredContentTypes() {

    $display_options = array();

    //Load Node Type configurations
    $node_types = NodeType::loadMultiple();

    foreach($node_types as $node_type_key => $node_type) {

      if($scheduler = $node_type->getThirdPartySettings('scheduler')) {

        if($scheduler['publish_enable'] == TRUE) {
          $display_options[$node_type_key] =  $node_type->label();
        }

      }
    }

    return $display_options;
  }

  /**
   * Build Content type configuration
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  protected function buildContentTypeConfiguration(array &$form, FormStateInterface $form_state) {

    //Get select options for content types
    $content_type_options = $this->getConfiguredContentTypes();

    //Get all config entities
    $entities = $this->contentTypeConfigService->loadAllEntities();

    //Get all config entities keys
    $entity_keys = array_keys($entities);

    $form['content_type_configuration'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Content Type Configuration'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    $form['content_type_configuration']['content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Which Content Types need to be displayed?'),
      '#description' => $this->t('Only Content Types enabled for Scheduler are listed here'),
      '#required' => TRUE,
      '#options' => $content_type_options,
      '#default_value' => $entity_keys,
    ];

    if($entities) {

      $rows = array();

      foreach($entities as $entity_key => $entity) {

        $options = array(
          'query' => array(
            'destination' => Url::fromRoute('content_calendar.settings')->toString()
          ),
        );

        $edit_link = Link::createFromRoute(
          $this->t('Configure'),
          'entity.content_type_config.edit_form',
          array('content_type_config' => $entity_key),
          $options
        );

        $row = array(
          $entity->label(),
          $entity->id(),
          $entity->getColor(),
          $edit_link->toString()
        );

        $rows[] = $row;
      }

      $headers = array(
        $this->t('Content Type'),
        $this->t('ID'),
        $this->t('Color'),
        $this->t('Actions')
      );

      $form['content_type_configuration']['table'] = array(
        '#type' => 'table',
        '#header' => $headers,
        '#rows' => $rows,
        '#weight' => 20
      );
    }

  }

  /**
   * Build the form elements for the calendar options
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  protected function buildCalendarOptions(array &$form, FormStateInterface $form_state) {

    //Fieldset
    $form['options'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Display Options'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    //Show user thumb checkbox
    $user_picture_field_exists = !$this->config('field.field.user.user.user_picture')->isNew();

    $form['options']['show_user_thumb'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show thumbnail image of User image'),
      '#description' => $this->t('This option is only available, if the User account has the "user_picture" field. See Account configuration.'),
      '#disabled' => !$user_picture_field_exists,
      '#default_value' => $this->config->get('show_user_thumb'),
    ];

    $form['options']['bg_color_unpublished_content'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background color for unpublished content'),
      '#description' => $this->t("Define the background color for unpublished content. Use a css color in word format (e.x. red) or a hexadecimal value (e.x. #ffcc00). When empty the default value will be used."),
      '#maxlength' => 20,
      '#default_value' => $this->config->get('bg_color_unpublished_content'),
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    //Get form values
    $values = $form_state->getValues();

    //Save show user image thumbnail option
    $this->config(self::$configName)
      ->set('show_user_thumb', $values['show_user_thumb'])
      ->set('bg_color_unpublished_content', $values['bg_color_unpublished_content'])
      ->save();

    //Get selected Content Types
    $selected_content_types = $this->getSelectedContentTypes($form_state);

    //Load config entities
    $config_entities = $this->contentTypeConfigService->loadAllEntities();

    //Check which config entity needs to be added
    $this->addNewConfigEntities($selected_content_types, $config_entities);

    //Check which config entity needs to be deleted
    $this->deleteObsoleteConfigEntities($selected_content_types, $config_entities);
  }

  /**
   * Get selected content types
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  protected function getSelectedContentTypes(FormStateInterface &$form_state) {

    //Get values
    $values = $form_state->getValues();

    //Save Content types to be displayed
    $selected_content_types = array();

    foreach($values['content_types'] as $key => $selected) {

      if($selected) {
        $selected_content_types[] = $key;
      }
    }

    return $selected_content_types;
  }

  /**
   * Check which config entity needs to be deleted
   *
   * @param array $selected_content_types
   * @param \Drupal\content_calendar\Entity\ContentTypeConfig[] $config_entities
   */
  protected function addNewConfigEntities(array $selected_content_types, array &$config_entities) {

    //Get entity keys
    $entity_keys = array_keys($config_entities);

    foreach($selected_content_types as $selected_content_type) {

      if(!in_array($selected_content_type, $entity_keys)) {

        if($node_type = NodeType::load($selected_content_type)) {
          $this->contentTypeConfigService->createEntity($selected_content_type, $node_type->label());
          $this->messenger()->addMessage(t('Content Type @name has been added and can be configured below.', array('@name' => $node_type->label())));
        }
      }
    }
  }

  /**
   * Check which config entity needs to be deleted
   *
   * @param array $selected_content_types
   * @param \Drupal\content_calendar\Entity\ContentTypeConfig[] $config_entities
   */
  protected function deleteObsoleteConfigEntities(array $selected_content_types, array &$config_entities) {

    foreach($config_entities as $config_entity_id => $config_entity) {

      if(!in_array($config_entity_id, $selected_content_types)) {
        $this->messenger()->addMessage(t('Content Type @name has been removed from Content Calendar.', array('@name' => $config_entity->label())));
        $config_entity->delete();
      }
    }

  }
}