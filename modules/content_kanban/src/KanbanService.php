<?php

namespace Drupal\content_kanban;

use Drupal\content_kanban\Form\SettingsForm;
use Drupal\content_moderation\Entity\ContentModerationState;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\node\Entity\NodeType;

/**
 * Class KanbanService.
 */
class KanbanService {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Drupal\content_moderation\ModerationInformationInterface definition.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $moderationInformation;

  /**
   * @var array
   */
  protected $definedColors = array(
    '#0074bd', //Drupal Standard color
    '#D66611',
    '#27E834',
    '#FF3D2A',
    'purple',
    '#22FFA0',
    'black',
    '#37C2FF'
  );

  /**
   * Constructs a new KanbanService object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    Connection $database,
    ModerationInformationInterface $moderation_information
  ) {
    $this->configFactory = $config_factory;
    $this->database = $database;
    $this->moderationInformation = $moderation_information;
  }


  /**
   * Check if the Content Calendar module is enabled
   *
   * @return bool
   */
  public function contentCalendarModuleIsEnabled() {
    return \Drupal::moduleHandler()->moduleExists('content_calendar');
  }

  /**
   * Get Kanban settings
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   */
  public function getKanbanSettings() {
    $settings = $this->configFactory->get(SettingsForm::$configName);

    return $settings;
  }

  /**
   * Check if the option to use the Content Calendar colors is active
   *
   * @return bool
   */
  public function useContentCalendarColors() {

    if($this->contentCalendarModuleIsEnabled()) {
      $settings = $this->getKanbanSettings();

      if($value = $settings->get('use_content_calendar_colors')) {
        return (bool) $value;
      }
    }

    return FALSE;
  }

  /**
   * Get Node Type Configs
   *
   * @param array $filter Filter which node types to include
   *
   * @return \Drupal\content_kanban\NodeTypeConfig[]
   */
  public function getNodeTypeConfigs($filter = array()) {

    $node_type_configs = array();

    //Load all Node types
    $node_types = NodeType::loadMultiple();

    $color_index = 0;
    foreach($node_types as $node_type_id => $entity) {

      if($filter && !in_array($node_type_id, $filter)) {
        continue;
      }

      $node_type_configs[$node_type_id] = new NodeTypeConfig(
        $node_type_id,
        $entity->label(),
        $this->getColor($color_index)
      );

      $color_index++;
    }

    //Overwrite defined colors with colors from Content Calendar
    if($this->useContentCalendarColors()) {

      /**
       * @var $content_type_config_service \Drupal\content_calendar\ContentTypeConfigService
       */
      $content_type_config_service = \Drupal::service('content_calendar.content_type_config_service');

      //Load Content Type configs from Content Calendar
      $content_type_configs = $content_type_config_service->loadAllEntities();

      //Overwrite colors
      foreach($content_type_configs as $content_type => $content_type_config) {

        if(array_key_exists($content_type, $node_type_configs)) {
          $node_type_configs[$content_type]->setColor($content_type_config->getColor());
        }
      }
    }

    return $node_type_configs;
  }

  /**
   * Get Color
   *
   * @param int $index
   *
   * @return string
   */
  protected function getColor($index) {

    //If the desired index is greater than the count of defined colors
    if(($index + 1) > count($this->definedColors)) {
      $index = 0;
    }

    return $this->definedColors[$index];
  }

  /**
   * Get Content Moderation Entities
   *
   * @param string $workflow The workflow ID
   * @param array $filters
   *
   * @return \Drupal\content_moderation\Entity\ContentModerationState[]
   */
  public function getNodeContentModerationEntities($workflow, $filters = array()) {

    $query = \Drupal::entityQuery('content_moderation_state');

    $query->condition('workflow', $workflow);
    $query->condition('content_entity_type_id', 'node');

    //Moderation state filter
    if(array_key_exists('moderation_state', $filters) && $filters['moderation_state']) {
      $query->condition('moderation_state', $filters['moderation_state']);
    }

    //User ID filter
    if(array_key_exists('uid', $filters) && $filters['uid']) {
      $node_query = \Drupal::entityQuery('node');
      $node_query->condition('uid', $filters['uid']);
      $node_result = $node_query->execute();

      if ($node_result && is_array($node_result) && count($node_result) > 0) {
        $query->condition('content_entity_id', $node_result, 'IN');
      }
      else {
        // keine nodes mit dieser uid
        return [];
      }
    }

    $result = $query->execute();

    if($result) {
      return ContentModerationState::loadMultiple($result);
    }

    return array();
  }

  /**
   * Get Node IDs from Content Moderation entities
   *
   * @param string $workflow
   * @param array $filters
   *
   * @return array
   */
  public function getNodeIDsFromContentModerationEntities($workflow, $filters = array()) {

    $nids = array();

    if($content_moderation_states = $this->getNodeContentModerationEntities($workflow, $filters)) {

      foreach($content_moderation_states as $content_moderation_state) {

        //Get property
        $content_entity_id_property = $content_moderation_state->content_entity_id;

        //Get value
        $content_entity_id_value = $content_entity_id_property->getValue();

        //Get Content ID / NID
        $nids[] = $content_entity_id_value[0]['value'];
      }

    }

    return $nids;
  }

  /**
   * Get Nodes by Type
   *
   * @param int $node_type
   * @param array $filters
   *
   * @return array
   */
  public function getNodesByNodeIDs($nids) {

    //Basic table
    $query = $this->database->select('node_field_data', 'nfd');

    //Joins
    $query->innerJoin('users_field_data', 'ufd', 'nfd.uid = ufd.uid');

    //Fields
    $query->addField('nfd', 'nid');
    $query->addField('nfd', 'title');
    $query->addField('nfd', 'created');
    $query->addField('nfd', 'status');
    $query->addField('nfd', 'type');
    $query->addField('nfd', 'uid');
    $query->addField('ufd', 'name', 'username');

    //Conditions
    $query->condition('nfd.nid', $nids, 'in');

    //Sort
    if($this->contentCalendarModuleIsEnabled()) {
      $query->orderBy('nfd.publish_on', 'ASC');
    } else {
      $query->orderBy('nfd.created', 'ASC');
    }

    $result = $query->execute()->fetchAll();

    if($result) {
      return $result;
    }

    return array();
  }

}
