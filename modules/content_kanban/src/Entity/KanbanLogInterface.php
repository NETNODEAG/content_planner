<?php

namespace Drupal\content_kanban\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Kanban Log entities.
 *
 * @ingroup content_kanban
 */
interface KanbanLogInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Kanban Log name.
   *
   * @return string
   *   Name of the Kanban Log.
   */
  public function getName();

  /**
   * Sets the Kanban Log name.
   *
   * @param string $name
   *   The Kanban Log name.
   *
   * @return \Drupal\content_kanban\Entity\KanbanLogInterface
   *   The called Kanban Log entity.
   */
  public function setName($name);

  /**
   * Gets the Kanban Log creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Kanban Log.
   */
  public function getCreatedTime();

  /**
   * Sets the Kanban Log creation timestamp.
   *
   * @param int $timestamp
   *   The Kanban Log creation timestamp.
   *
   * @return \Drupal\content_kanban\Entity\KanbanLogInterface
   *   The called Kanban Log entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Kanban Log published status indicator.
   *
   * Unpublished Kanban Log are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Kanban Log is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Kanban Log.
   *
   * @param bool $published
   *   TRUE to set this Kanban Log to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\content_kanban\Entity\KanbanLogInterface
   *   The called Kanban Log entity.
   */
  public function setPublished($published);

}
