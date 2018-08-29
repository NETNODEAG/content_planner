<?php

namespace Drupal\content_kanban\Controller;

use Drupal\content_kanban\KanbanService;
use Drupal\content_kanban\Component\Kanban;
use Drupal\content_kanban\KanbanWorkflowService;
use Drupal\content_moderation\ModerationInformation;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\workflows\Entity\Workflow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class KanbanController.
 */
class KanbanController extends ControllerBase {

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\content_kanban\KanbanService
   */
  protected $kanbanService;

  /**
   * @var \Drupal\content_moderation\ModerationInformation
   */
  protected $moderationInformation;

  /**
   * Constructs a new KanbanController object.
   */
  public function __construct(
    AccountInterface $current_user,
    KanbanService $kanban_service,
    ModerationInformation $moderation_information
  ) {
    $this->currentUser = $current_user;
    $this->kanbanService = $kanban_service;
    $this->moderationInformation = $moderation_information;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('content_kanban.kanban_service'),
      $container->get('content_moderation.moderation_information')
    );
  }

  /**
   * Show Kanbans
   *
   * @return array
   * @throws \Exception
   */
  public function showKanbans() {

    $build = array();

    $workflows = Workflow::loadMultiple();

    if(!$workflows) {
      $this->messenger()->addMessage($this->t('There are no Workflows configured yet.'), 'error');
      return array();
    }

    foreach($workflows as $workflow) {

      if(Kanban::isValidContentModerationWorkflow($workflow)) {

        $kanban = new Kanban(
          $this->currentUser,
          $this->kanbanService,
          $workflow
        );

        $build[] = $kanban->build();
      }

    }

    //If there are no Kanbans, display a message.
    if(!$build) {

      $link = Url::fromRoute('entity.workflow.collection')->toString();

      $message = $this->t('To use Content Kanban, you need to have a valid Content Moderation workflow with at least one Node Type configured. Please go to the <a href="@link">Workflow</a> configuration.', array('@link' => $link));
      $this->messenger()->addMessage($message, 'error');
    }

    return $build;
  }


  /**
   * Update Workflow state of a given Node
   *
   * @param \Drupal\node\NodeInterface $node
   * @param string $state_id
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateNodeWorkflowState(NodeInterface $node, $state_id) {

    $data = array(
      'success' => FALSE,
      'message' => NULL,
    );

    //Check if entity is moderated
    if(!$this->moderationInformation->isModeratedEntity($node)) {

      $data['message'] = $this->t('Node with ID @id is not a moderated entity.', array('@id' => $node->id()));
      return new JsonResponse($data);
    }

    //Get Workflow from entity
    $workflow = $this->moderationInformation->getWorkflowForEntity($node);

    //If Workflow does not exist
    if(!$workflow) {
      $data['message'] = $this->t('Workflow not found for Node with ID @id.', array('@id' => $node->id()));
      return new JsonResponse($data);
    }

    //Get Workflow States
    $workflow_states = KanbanWorkflowService::getWorkflowStates($workflow);

    //Check if state given by request matches any of the Workflow's states
    if(!array_key_exists($state_id, $workflow_states)) {

      $data['message'] = $this->t(
        'Workflow State @state_id is not a valid state of Workflow @workflow_id.',
        array(
          '@state_id' => $state_id,
          '@workflow_id' => $workflow->id(),
        )
      );
      return new JsonResponse($data);
    }

    //Set new state
    $node->moderation_state->value = $state_id;

    //Save
    if($node->save() == SAVED_UPDATED) {
      $data['success'] = TRUE;
      $data['message'] = $this->t(
        'Workflow state of Node @id has been updated to @state_id',
        array(
          '@id' => $node->id(),
          '@state_id' => $state_id,
        )
      );
    }

    return new JsonResponse($data);
  }

}
