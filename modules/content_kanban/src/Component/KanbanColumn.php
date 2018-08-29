<?php

namespace Drupal\content_kanban\Component;

class KanbanColumn {

  /**
   * @var string
   */
  protected $workflowID;

  /**
   * @var string
   */
  protected $stateID;

  /**
   * @var array
   */
  protected $stateInfo = array();

  /**
   * @var array
   */
  protected $nodes = array();

  /**
   * @var array|\Drupal\content_kanban\NodeTypeConfig[]
   */
  protected $nodeTypeConfigs = array();

  /**
   * Constructor.
   *
   * @param string $workflow_id
   * @param string $state_id
   * @param array $state_info
   * @param array $nodes
   * @param \Drupal\content_kanban\NodeTypeConfig[] $node_type_configs
   */
  public function __construct(
    $workflow_id,
    $state_id,
    array $state_info,
    array $nodes,
    array $node_type_configs
  ) {

    $this->workflowID = $workflow_id;
    $this->stateID = $state_id;
    $this->stateInfo = $state_info;
    $this->nodes = $nodes;
    $this->nodeTypeConfigs = $node_type_configs;
  }

  /**
   * Build
   *
   * @return array
   */
  public function build() {

    $node_builds = array();

    foreach($this->nodes as $node) {

      $kanban_entry = new KanbanEntry(
        $node,
        $this->stateID,
        $this->nodeTypeConfigs[$node->type]
      );

      $node_builds[] = $kanban_entry->build();
    }

    $build = array(
      '#theme' => 'content_kanban_column',
      '#column_id' => $this->workflowID . '-' . $this->stateID,
      '#workflow_id' => $this->workflowID,
      '#state_id' => $this->stateID,
      '#state_label' => $this->stateInfo['label'],
      '#nodes' => $node_builds
    );

    return $build;
  }


}