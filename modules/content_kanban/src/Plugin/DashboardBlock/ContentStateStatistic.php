<?php

/**
 * @file
 * Contains \Drupal\content_planner\Plugin\DashboardBlock\UserBlock.
 */

namespace Drupal\content_kanban\Plugin\DashboardBlock;

use Drupal\content_planner\DashboardBlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\workflows\Entity\Workflow;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a Dashboard block for Content Planner Dashboard
 *
 * @DashboardBlock(
 *   id = "content_state_statistic",
 *   name = @Translation("Content State Statistic")
 * )
 */
class ContentStateStatistic extends DashboardBlockBase {

  /**
   * @var \Drupal\content_kanban\KanbanStatisticService
   */
  protected $kanbanStatisticService;

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->kanbanStatisticService = \Drupal::service('content_kanban.kanban_statistic_service');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigSpecificFormFields(FormStateInterface &$form_state,
                                              Request &$request,
                                              array $block_configuration) {

    $form = array();

    $workflow_options = array();

    //Get all workflows
    $workflows = Workflow::loadMultiple();

    /**
     * @var $workflow \Drupal\workflows\Entity\Workflow
     */
    foreach($workflows as $workflow) {

      if($workflow->status()) {
        $workflow_options[$workflow->id()] = $workflow->label();
      }
    }

    $form['workflow_id'] = array(
      '#type' => 'select',
      '#title' => t('Select workflow'),
      '#required' => TRUE,
      '#options' => $workflow_options,
      '#default_value' => $this->getCustomConfigByKey($block_configuration, 'workflow_id', ''),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    //Get config
    $config = $this->getConfiguration();

    //Get Workflow ID from config
    $workflow_id = $this->getCustomConfigByKey($config, 'workflow_id', '');

    //Load workflow
    $workflow = Workflow::load($workflow_id);

    //If workflow does not exist
    if(!$workflow) {
      $message = t('Content Status Statistic: Workflow with ID @id does not exist. Block will not be shown.', array('@id' => $workflow_id));
      drupal_set_message($message, 'error');
      return array();
    }

    //Get data
    $data = $this->kanbanStatisticService->getWorkflowStateContentCounts($workflow);

    $build = array(
      '#theme' => 'content_state_statistic',
      '#data' => $data,
      '#attached' => array(
        'library' => array('content_kanban/content_state_statistic')
      ),
    );

    return $build;
  }

}
