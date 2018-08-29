<?php

namespace Drupal\content_kanban\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\Access\AccessInterface;

/**
 * Class AccessCheck.
 *
 * @package Drupal\my_module\Access
 */
class AccessCheck implements AccessInterface {

  /**
   * Check if user can access the Content Kanban
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   */
  public function canAccessContentKanban(AccountInterface $account) {

    if($account->hasPermission('manage own content with content kanban') ||
      $account->hasPermission('manage any content with content kanban')) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}