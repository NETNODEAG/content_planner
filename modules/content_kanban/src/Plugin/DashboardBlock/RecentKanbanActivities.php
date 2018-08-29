<?php

/**
 * @file
 * Contains \Drupal\content_planner\Plugin\DashboardBlock\UserBlock.
 */

namespace Drupal\content_kanban\Plugin\DashboardBlock;

use Drupal\content_kanban\Entity\KanbanLog;
use Drupal\content_planner\DashboardBlockBase;
use Drupal\content_planner\UserProfileImage;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\content_kanban\KanbanWorkflowService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a user block for Content Planner Dashboard
 *
 * @DashboardBlock(
 *   id = "recent_kanban_activities",
 *   name = @Translation("Recent Kanban Activities")
 * )
 */
class RecentKanbanActivities extends DashboardBlockBase {

  /**
   * @var int
   */
  protected $defaultLimit = 10;

  /**
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->dateFormatter = \Drupal::service('date.formatter');

  }

  /**
   * {@inheritdoc}
   */
  public function getConfigSpecificFormFields(FormStateInterface &$form_state,
                                              Request &$request,
                                              array $block_configuration) {

    $form = array();

    $limit_default_value = $this->getCustomConfigByKey($block_configuration, 'limit', $this->defaultLimit);

    //Limit
    $form['limit'] = array(
      '#type' => 'number',
      '#title' => t('Quantity'),
      '#required' => TRUE,
      '#default_value' => $limit_default_value,
    );

    $user_picture_field_exists = !\Drupal::config('field.field.user.user.user_picture')->isNew();

    $show_user_thumb_default_value = $limit_default_value = $this->getCustomConfigByKey($block_configuration, 'show_user_thumb', 0);

    $form['show_user_thumb'] = [
      '#type' => 'checkbox',
      '#title' => t('Show thumbnail image of User image'),
      '#description' => t('This option is only available, if the User account has the "user_picture" field. See Account configuration.'),
      '#disabled' => !$user_picture_field_exists,
      '#default_value' => $show_user_thumb_default_value,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $build = array();

    //Get config
    $config = $this->getConfiguration();

    //Get limit
    $limit = $this->getCustomConfigByKey($config, 'limit', $this->defaultLimit);

    /**
     * @var $kanban_log_service \Drupal\content_kanban\KanbanLogService
     */
    $kanban_log_service = \Drupal::service('content_kanban.kanban_log_service');

    //Get Logs
    if($logs = $kanban_log_service->getRecentLogs($limit, array('exclude_anonymous_users' => TRUE))) {
      $entries = $this->buildKanbanLogActivities($logs);

      $build = array(
        '#theme' => 'content_kanban_log_recent_activity',
        '#entries' => $entries,
        '#show_user_thumb' => $this->getCustomConfigByKey($config, 'show_user_thumb', 0),
      );

    }


    return $build;
  }

  /**
   * Build log entries
   *
   * @param array $logs
   *
   * @return array
   */
  protected function buildKanbanLogActivities(array $logs) {

    $entries = array();

    foreach($logs as $log) {

      //Get User object
      $user = $log->getOwner();

      //Get Node object
      $node = $log->getNode();

      //If the Node or user cannot be found, then continue with the next log
      if(!$node || !$user) {
        continue;
      }

      if($message = $this->composeMessage($log, $user, $node)) {

        $entry = array(
          'user_profile_image' => UserProfileImage::generateProfileImageURL($user, 'content_kanban_user_thumb'),
          'username' => $user->getAccountName(),
          'message' => $message,
        );

        $entries[] = $entry;

      }

    }

    return $entries;
  }

  /**
   * Compose message
   *
   * @param \Drupal\content_kanban\Entity\KanbanLog $log
   *
   * @return bool
   */
  protected function composeMessage(KanbanLog $log, User $user, Node $node) {

    $state_from = $log->getStateFrom();
    $state_to = $log->getStateTo();
    $workflow_states = KanbanWorkflowService::getWorkflowStates($log->getWorkflow());

    $node_link = Link::createFromRoute($node->getTitle(), 'entity.node.canonical', array('node' => $node->id()))->toString();

    if($state_from == $state_to) {

      $message = t(
        '@username has updated "@node" @time ago',
        array(
          '@username' => $user->getAccountName(),
          '@node' => $node_link,
          '@time' => $this->calculateTimeAgo($log),
        )
      );

    } else {

      $message = t(
        '@username has changed the state of "@node" from "@state_from" to "@state_to" @time ago',
        array(
          '@username' => $user->getAccountName(),
          '@node' => $node_link,
          '@time' => $this->calculateTimeAgo($log),
          '@state_from' => $workflow_states[$state_from],
          '@state_to' => $workflow_states[$state_to],
        )
      );

    }

    return $message;
  }

  /**
   * @param \Drupal\content_kanban\Entity\KanbanLog $log
   *
   * @return mixed
   */
  protected function calculateTimeAgo(KanbanLog $log) {
    return $this->dateFormatter->formatTimeDiffSince($log->getCreatedTime());
  }

}
