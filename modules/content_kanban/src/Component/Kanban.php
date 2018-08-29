<?php

namespace Drupal\content_kanban\Component;

use Drupal\content_kanban\Form\KanbanFilterForm;
use Drupal\content_kanban\KanbanService;
use Drupal\Core\Session\AccountInterface;
use Drupal\workflows\Entity\Workflow;

class Kanban {

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\content_kanban\KanbanService
   */
  protected $kanbanService;

  /**
   * @var \Drupal\workflows\Entity\Workflow
   */
  protected $workflow;

  /**
   * @var string
   */
  protected $workflowID;

  /**
   * @var array
   */
  protected $typeSettings = array();

  /**
   * @var array
   */
  protected $nodeTypes = array();

  /**
   * @var array
   */
  protected $states = array();

  public function __construct(
    AccountInterface $current_user,
    KanbanService $kanban_service,
    Workflow $workflow
  ) {

    if(!self::isValidContentModerationWorkflow($workflow)) {
      throw new \Exception('The given workflow is no valid Content Moderation Workflow');
    }

    //Store request object
    $this->request = \Drupal::request();

    //Store current user
    $this->currentUser = $current_user;

    //Store Kanban service
    $this->kanbanService = $kanban_service;

    //Store Workflow
    $this->workflow = $workflow;

    //Store Workflow ID
    $this->workflowID = $workflow->get('id');

    //Store Type settings
    $this->typeSettings = $workflow->get('type_settings');

    //Store Node types this workflow applies to
    $this->nodeTypes = $this->typeSettings['entity_types']['node'];

    //Store states
    $this->states = $this->sortStates($this->typeSettings['states']);
  }

  /**
   * @param array $states
   *
   * @return array
   */
  protected function sortStates($states) {

    //Make a copy of the states
    $sorted_states = $states;

    //Add the state id, so it does not get lost during the custom sort function
    foreach($sorted_states as $state_id => &$state) {
      $state['state_id'] = $state_id;
    }

    //Sort for weight
    usort($sorted_states, function($a, $b) {
      if ($a['weight'] == $b['weight']) {
        return 0;
      } else if ($a['weight'] < $b['weight']) {
        return -1;
      } else {
        return 1;
      }
    });

    //Build a new return array
    $return = array();

    foreach($sorted_states as $sorted_state) {
      $return[$sorted_state['state_id']] = $sorted_state;
    }

    return $return;
  }

  /**
   * Check if a given workflow is a valid Content Moderation workflow
   *
   * @param \Drupal\workflows\Entity\Workflow $workflow
   *
   * @return bool
   */
  public static function isValidContentModerationWorkflow(Workflow $workflow) {

    if($workflow->get('type') == 'content_moderation') {

      $type_settings = $workflow->get('type_settings');

      if(array_key_exists('node', $type_settings['entity_types'])) {

        if(array_key_exists('states', $type_settings)) {

          if(!empty($type_settings['states'])) {
            return TRUE;
          }


        }
      }

    }

    return FALSE;
  }

  /**
   * Build
   *
   * @return array
   */
  public function build() {

    $columns = array();

    //Get all Node Type configs
    $node_type_configs = $this->kanbanService->getNodeTypeConfigs($this->nodeTypes);

    //Get User ID filter
    $filter_uid = KanbanFilterForm::getUserIDFilter();

    //If the user cannot edit any content, hide the Filter form
    if(!$this->currentUser->hasPermission('manage any content with content kanban')) {
      $filter_uid = $this->currentUser->id();
    }

    //Get State filter
    $filter_state = KanbanFilterForm::getStateFilter();

    foreach($this->states as $state_id => $state) {

      //If the State filter has been set, only get data which set by the filter
      if($filter_state && $filter_state != $state_id) {
        $columns[] = array(
          'nodes' => array(),
        );
        continue;
      }

      //Prepare filter for the Kanban service
      $filters = array(
        'moderation_state' => $state_id,
      );

      //Add User filter, if given
      if($filter_uid) {
        $filters['uid'] = $filter_uid;
      }

      //Get Node IDs
      if($nids = $this->kanbanService->getNodeIDsFromContentModerationEntities($this->workflowID, $filters)) {
        $nodes = $this->kanbanService->getNodesByNodeIDs($nids);
      } else {
        $nodes = array();
      }

      //Create Kanban object
      $kanban_column = new KanbanColumn(
        $this->workflowID,
        $state_id,
        $state,
        $nodes,
        $node_type_configs
      );

      //Build render array for Kanban
      $columns[] = $kanban_column->build();
    }

    //Permissions
    $permissions = array(
      'create_node' => $this->getCreateNodePermissions($node_type_configs),
    );

    $build = array(
      '#theme' => 'content_kanban',
      '#kanban_id' => $this->workflowID,
      '#kanban_label' => $this->workflow->label(),
      '#filter_form' => $this->buildFilterForm(),
      '#permissions' => $permissions,
      '#headers' => $this->buildHeaders(),
      '#columns' => $columns,
      '#attached' => array(
        'library' => array('content_kanban/kanban')
      ),
    );

    return $build;

  }

  /**
   * Build headers for table
   *
   * @return array
   */
  protected function buildHeaders() {

    $headers = array();

    foreach($this->states as $state_id => $state) {
      $headers[] = $state['label'];
    }

    return $headers;
  }

  /**
   * Get list of permissions the current user may create a Node type
   *
   * @param \Drupal\content_kanban\NodeTypeConfig[] $node_type_configs
   *
   * @return array
   */
  protected function getCreateNodePermissions($node_type_configs) {

    $permissions = array();

    foreach($node_type_configs as $node_type => $node_type_config) {

      //Check if the current user has the permisson to create a certain Node type
      if($this->currentUser->hasPermission("create $node_type content")) {
        $permissions[$node_type] = t("Add @type", array('@type' => $node_type_config->getLabel()));
      }

    }

    return $permissions;
  }

  /**
   * Build Filter form
   *
   * @return array
   */
  protected function buildFilterForm() {

    //If the user cannot edit any content, hide the Filter form
    if(!$this->currentUser->hasPermission('manage any content with content kanban')) {
      return array();
    }

    //Get Filter form
    $form_params = array(
      'workflow_id' => $this->workflowID,
      'states' => $this->states,
    );
    $filter_form = \Drupal::formBuilder()->getForm('Drupal\content_kanban\Form\KanbanFilterForm', $form_params);

    //Remove certain needed form properties
    unset($filter_form['form_build_id']);
    unset($filter_form['form_id']);

    return $filter_form;
  }
}