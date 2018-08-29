<?php

namespace Drupal\content_calendar;

use Drupal\content_calendar\ContentTypeConfigService;
use Drupal\Core\Database\Driver\mysql\Connection;

/**
 * Class ContentCalendarService.
 */
class ContentCalendarService {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Drupal\content_calendar\ContentTypeConfigService definition.
   *
   * @var \Drupal\content_calendar\ContentTypeConfigService
   */
  protected $contentTypeConfigService;

  /**
   * Constructs a new ContentCalendarService object.
   */
  public function __construct(
    Connection $database,
    ContentTypeConfigService $content_type_config_service
  ) {
    $this->database = $database;
    $this->contentTypeConfigService = $content_type_config_service;
  }

  /**
   * Get Nodes by Type
   *
   * @param int $node_type
   * @param array $filters
   *
   * @return array
   */
  public function getNodesByType($node_type, $filters = array()) {

    //Basic table
    $query = $this->database->select('node_field_data', 'nfd');

    //Joins
    $query->innerJoin('users_field_data', 'ufd', 'nfd.uid = ufd.uid');

    //Fields
    $query->addField('nfd', 'nid');
    $query->addField('nfd', 'title');
    $query->addField('nfd', 'created');
    $query->addField('nfd', 'status');
    $query->addField('nfd', 'uid');
    $query->addField('ufd', 'name', 'username');
    $query->addField('nfd', 'publish_on');

    //Conditions
    $query->condition('nfd.type', $node_type);
    //$query->isNotNull('nfd.publish_on');

    //Sort
    $query->orderBy('nfd.created', 'ASC');

    //Add year filter
    if(isset($filters['year']) && $filters['year'] && is_numeric($filters['year'])) {

      if(isset($filters['month']) && $filters['month'] && is_numeric($filters['month'])) {
        $month_from = $filters['month'];
        $month_to = $filters['month'];
      } else {
        $month_from = 1;
        $month_to = 12;
      }

      //From datetime
      $datetime_from = DateTimeHelper::getFirstDayOfMonth($month_from, $filters['year']);

      //To datetime
      $datetime_to = DateTimeHelper::getLastDayOfMonth($month_to, $filters['year']);

      $or_conditions = $query->orConditionGroup();


      //Date range conditions for scheduler date
      $scheduler_date_conditions = $query->andConditionGroup();
      $scheduler_date_conditions->condition('nfd.publish_on', $datetime_from->format('U'), '>=');
      $scheduler_date_conditions->condition('nfd.publish_on', $datetime_to->format('U'), '<=');
      $or_conditions->condition($scheduler_date_conditions);

      //Date range conditions for created date
      $created_date_conditions = $query->andConditionGroup();
      $created_date_conditions->condition('nfd.created', $datetime_from->format('U'), '>=');
      $created_date_conditions->condition('nfd.created', $datetime_to->format('U'), '<=');
      $or_conditions->condition($created_date_conditions);

      $query->condition($or_conditions);
    }

    $result = $query->execute()->fetchAll();

    if($result) {
      return $result;
    }

    return array();
  }

}
