<?php

namespace Drupal\content_kanban;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Kanban Log entity.
 *
 * @see \Drupal\content_kanban\Entity\KanbanLog.
 */
class KanbanLogAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\content_kanban\Entity\KanbanLogInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished kanban log entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published kanban log entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit kanban log entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete kanban log entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add kanban log entities');
  }

}
