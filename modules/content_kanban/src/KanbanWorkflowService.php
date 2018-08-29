<?php

namespace Drupal\content_kanban;

use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\workflows\Entity\Workflow;

/**
 * Class KanbanWorkflowService.
 */
class KanbanWorkflowService {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $moderationInformation;

  /**
   * @var \Drupal\content_kanban\KanbanLogService
   */
  protected $kanbanLogService;

  /**
   * Constructs a new NewsService object.
   */
  public function __construct(
    Connection $database,
    ModerationInformationInterface $moderation_information,
    KanbanLogService $kanban_log_service
  ) {
    $this->database = $database;
    $this->moderationInformation = $moderation_information;
    $this->kanbanLogService = $kanban_log_service;
  }

  /**
   * Act upon a node presave
   *
   * @see content_kanban_node_presave()
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function onNodePresave(
    ContentEntityInterface $entity,
    AccountInterface $user
  ) {

    //When Entity Type is node
    if($entity->getEntityTypeId() == 'node') {

      //If the entity is moderated, meaning it belongs to a certain workflow
      if($this->moderationInformation->isModeratedEntity($entity)) {

        $current_state = $this->getCurrentStateID($entity);

        $prev_state = $this->getPreviousWorkflowStateID($entity);

        if($current_state && $prev_state) {

          //Generate name for entity
          $name = t('Workflow State change on Node')->render();

          //Get workflow from moderated entity
          $workflow = $this->moderationInformation->getWorkflowForEntity($entity);

          //Create new log entity
          $this->kanbanLogService->createLogEntity(
            $name,
            $user->id(),
            $entity->id(),
            $workflow->id(),
            $prev_state,
            $current_state
          );

        }


      }

    }
  }


  /**
   * Get current State ID
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *
   * @return mixed
   */
  public function getCurrentStateID(ContentEntityInterface $entity) {
    return $entity->moderation_state->value;
  }

  /**
   * Get the label of the current state of a given entity
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *
   * @return bool|string
   */
  public function getCurrentStateLabel(ContentEntityInterface $entity) {

    if($this->moderationInformation->isModeratedEntity($entity)) {

      if($workflow = $this->moderationInformation->getWorkflowForEntity($entity)) {

        if($states = self::getWorkflowStates($workflow)) {

          $entity_workflow_state = $this->getCurrentStateID($entity);

          if(array_key_exists($entity_workflow_state, $states)) {
            return $states[$entity_workflow_state];
          }
        }
      }

    }

    return FALSE;
  }

  /**
   * Get Workflow States
   *
   * @param \Drupal\workflows\Entity\Workflow $workflow
   *
   * @return array
   */
  public static function getWorkflowStates(Workflow $workflow) {

    $states = array();

    $type_settings = $workflow->get('type_settings');

    //Sort by weight
    uasort($type_settings['states'], function($a, $b) {

      if ($a['weight'] == $b['weight']) {
        return 0;
      } else if ($a['weight'] < $b['weight']) {
        return -1;
      } else {
        return 1;
      }

    });

    foreach($type_settings['states'] as $state_id => $state) {
      $states[$state_id] = $state['label'];
    }

    return $states;
  }


  /**
   * Get ID of the previous workflow state
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *
   * @return string
   */
  public function getPreviousWorkflowStateID(ContentEntityInterface $entity)  {

    $workflow = $this->moderationInformation->getWorkflowForEntity($entity);

    if($state_history = $this->getWorkflowStateHistory($workflow->id(), $entity)) {

      if(isset($state_history[0])) {
        return $state_history[0];
      }
    }

    return 'draft';
  }

  /**
   * Get the workflow state history of a given node
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   */
  public function getWorkflowStateHistory($workflow_id, ContentEntityInterface $entity) {

    $query = $this->database->select('content_moderation_state_field_revision', 'r');

    $query->addField('r', 'moderation_state');

    $query->condition('r.workflow', $workflow_id);
    $query->condition('r.content_entity_type_id', 'node');
    $query->condition('r.content_entity_id', $entity->id());

    $query->orderBy('r.revision_id', 'DESC');

    $result = $query->execute()->fetchAll();

    if(!$result) {
      return array();
    } else {

      $return = array();

      foreach($result as $row) {
        $return[] = $row->moderation_state;
      }

      return $return;
    }
  }

}
