<?php

namespace Drupal\content_kanban;
use Drupal\content_kanban\Entity\KanbanLog;
use Drupal\Core\Database\Driver\mysql\Connection;

/**
 * Class KanbanLogService.
 */
class KanbanLogService {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Constructs a new KanbanLogService object.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Create new log entry
   *
   * @param string $name
   * @param int $user_id
   * @param int $nid
   * @param string $workflow_id
   * @param null|string $state_from
   * @param null|string $state_to
   *
   * @return int
   */
  public function createLogEntity($name, $user_id, $nid, $workflow_id, $state_from = NULL, $state_to = NULL) {

    $entity_build = array(
      'name' => $name,
      'user_id' => $user_id,
      'nid' => $nid,
      'workflow_id' => $workflow_id,
      'state_from' => $state_from,
      'state_to' => $state_to,
    );

    $entity = KanbanLog::create($entity_build);

    return $entity->save();
  }

  /**
   * Get recent Logs
   *
   * @param int $limit
   *
   * @return \Drupal\content_kanban\Entity\KanbanLog[]
   */
  public function getRecentLogs($limit = 10, $filter = array()) {

    $query = \Drupal::entityQuery('content_kanban_log');
    $query->sort('created', 'DESC');
    $query->range(0, $limit);

    if(isset($filter['exclude_anonymous_users']) && $filter['exclude_anonymous_users'] == TRUE) {
      $query->condition('user_id', 0, '<>');
    }

    $result = $query->execute();

    if($result) {
      return KanbanLog::loadMultiple($result);
    }

    return array();
  }

}
