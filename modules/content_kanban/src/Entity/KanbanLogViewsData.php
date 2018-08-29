<?php

namespace Drupal\content_kanban\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Kanban Log entities.
 */
class KanbanLogViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
