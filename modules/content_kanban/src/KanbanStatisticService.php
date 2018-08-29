<?php

namespace Drupal\content_kanban;

use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\workflows\Entity\Workflow;

/**
 * Class KanbanStatisticService.
 */
class KanbanStatisticService {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new NewsService object.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Get content counts from a given Workflow
   *
   * @param \Drupal\workflows\Entity\Workflow $workflow
   *
   * @return array
   */
  public function getWorkflowStateContentCounts(Workflow $workflow) {

    //Get all workflow states form a given workflow
    $workflow_states = KanbanWorkflowService::getWorkflowStates($workflow);

    $data = array();

    foreach($workflow_states as $state_id => $state_label) {

      $count = $this->getWorkflowStateContentCount($workflow->id(), $state_id);

      $data[$state_id] = array(
        'id' => $state_id,
        'label' => $state_label,
        'count' => $count,
      );
    }

    return $data;
  }

  /**
   * Get the content count of a given workflow state
   *
   * @param string $workflow_id
   * @param string $state_id
   *
   * @return mixed
   */
  public function getWorkflowStateContentCount($workflow_id, $state_id) {

    $query = $this->database->select('content_moderation_state_field_data', 'c');

    $query->addField('c', 'id');

    $query->condition('c.workflow', $workflow_id);
    $query->condition('c.moderation_state', $state_id);

    $count_query = $query->countQuery();

    $result = $count_query->execute()->fetchObject();

    return $result->expression;
  }

}
