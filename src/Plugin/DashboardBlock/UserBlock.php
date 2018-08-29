<?php

/**
 * @file
 * Contains \Drupal\content_planner\Plugin\DashboardBlock\UserBlock.
 */

namespace Drupal\content_planner\Plugin\DashboardBlock;

use Drupal\content_planner\DashboardBlockBase;
use Drupal\content_planner\UserProfileImage;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a user block for Content Planner Dashboard
 *
 * @DashboardBlock(
 *   id = "user_block",
 *   name = @Translation("User Block")
 * )
 */
class UserBlock extends DashboardBlockBase {

  public function build() {

    $config = $this->getConfiguration();

    $users = $this->getUsers($config);

    if($users) {

      $roles = Role::loadMultiple();

      $user_data = array();

      foreach($users as $user) {

        $user_data[] = array(
          'name' => $user->label(),
          'image' => UserProfileImage::generateProfileImageURL($user, 'content_planner_user_block_profile_image'),
          'roles' => implode(', ', $this->getUserRoles($user, $roles)),
          'content_count' => $this->getUserContentCount($user->id()),
          'content_kalendertag_count' => $this->getUserContentWorkflowCount($user->id(), 'am_kalendertag_publizieren'),
          'content_draft_count' => $this->getUserContentWorkflowCount($user->id(), 'draft'),
        );

      }

      return array(
        '#theme' => 'content_planner_dashboard_user_block',
        '#users' => $user_data
      );
    }


    return array();
  }

  /**
   * @param array $config
   *
   * @return \Drupal\user\Entity\User[]
   */
  protected function getUsers(&$config) {

    //Get configured roles
    $configured_roles = $config['plugin_specific_config']['roles'];

    $query = \Drupal::entityQuery('user');
    $query->condition('roles', array_values($configured_roles), 'in');
    $query->sort('access', 'desc');

    $result = $query->execute();

    if($result) {
      return User::loadMultiple($result);
    }

    return array();
  }

  /**
   * Get content count for a given user
   *
   * @param int $user_id
   *
   * @return int
   */
  protected function getUserContentCount($user_id) {

    $query = \Drupal::database()->select('node_field_data', 'nfd');
    $query->fields('nfd', array('nid'));
    $query->condition('nfd.uid', $user_id);
    $query->countQuery();

    $result = $query->execute();

    $result->allowRowCount = TRUE;

    $count = $result->rowCount();

    if($count) {
      return $count;
    }

    return 0;
  }

  /**
   * Get content count for a given user based on workflow status
   *
   * @param int $user_id
   * @param string $moderation_state
   *
   * @return int
   */
  public function getUserContentWorkflowCount($user_id, $moderation_state) {
    $kanban_service = \Drupal::service('content_kanban.kanban_service');

    $filters = [
      'uid' => $user_id,
      'moderation_state' => $moderation_state,
    ];
    $nids = $kanban_service->getNodeIDsFromContentModerationEntities('netnode', $filters);

    return count($nids);
  }


  /**
   * Get roles for a given user
   *
   * @param \Drupal\user\Entity\User $user
   * @param $roles
   *
   * @return array
   */
  protected function getUserRoles(User &$user, &$roles) {

    $user_roles = array();

    foreach($user->getRoles() as $role_id) {

      if($role_id != 'authenticated') {
        $user_roles[] = $roles[$role_id]->label();
      }
    }

    return $user_roles;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigSpecificFormFields(FormStateInterface &$form_state,
                                              Request &$request,
                                              array $block_configuration) {

    $form = array();

    //Build Role selection box
    $form['roles'] = $this->buildRoleSelectBox($form_state, $request, $block_configuration);

    return $form;
  }

  /**
   * Build Role select box
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Symfony\Component\HttpFoundation\Request|NULL $request
   * @param array $block_configuration
   *
   * @return array
   */
  protected function buildRoleSelectBox(FormStateInterface &$form_state,
                                        Request &$request,
                                        array $block_configuration) {

    //Get Roles
    $roles = Role::loadMultiple();

    $roles_options = array();

    foreach($roles as $role_id => $role) {

      if(in_array($role_id, array('anonymous'))) {
        continue;
      }

      $roles_options[$role_id] = $role->label();
    }

    $default_value = (isset($block_configuration['plugin_specific_config']['roles'])) ? $block_configuration['plugin_specific_config']['roles'] : array();

    return array(
      '#type' => 'checkboxes',
      '#title' => t('Which Roles to display'),
      '#description' => t('Select which Roles should be displayed in the block.'),
      '#required' => TRUE,
      '#options' => $roles_options,
      '#default_value' => $default_value,
    );
  }

}
