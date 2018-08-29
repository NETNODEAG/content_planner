<?php

namespace Drupal\content_kanban\Form;

use Drupal\content_kanban\KanbanService;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class KanbanFilterForm extends FormBase {

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * @var \Drupal\content_kanban\KanbanService
   */
  protected $kanbanService;

  /**
   * @var array
   */
  protected $formParams = array();

  /**
   * {@inheritdoc}
   */
  public function __construct(KanbanService $kanban_service) {
    $this->request = \Drupal::request();
    $this->kanbanService = $kanban_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('content_kanban.kanban_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'conent_kanban_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $params = array()) {

    $this->formParams = $params;

    $form_state->setMethod('GET');

    $form['filters'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Filters'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    //User ID
    $form['filters']['filter_uid'] = [
      '#type' => 'select',
      '#title' => $this->t('User'),
      '#description' => $this->t('Filter by User. Only Users with at least one moderated content are listed here.'),
      '#options' => $this->getUserOptions(),
      '#required' => FALSE,
      '#empty_value' => '',
      '#empty_option' => $this->t('- Select -'),
      '#default_value' => self::getUserIDFilter(),
    ];

    //User ID
    $form['filters']['filter_state'] = [
      '#type' => 'select',
      '#title' => $this->t('States'),
      '#options' => $this->getStateOptions(),
      '#required' => FALSE,
      '#empty_value' => '',
      '#empty_option' => $this->t('All'),
      '#default_value' => self::getStateFilter(),
    ];

    //Actions
    $form['filters']['actions'] = [
      '#type' => 'actions',
    ];

    //Submit button
    $form['filters']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];

//    $form['filters']['actions']['reset'] = [
//      '#type' => 'submit',
//      '#value' => $this->t('Reset'),
//    ];

    $form['filters']['actions']['reset'] = [
      '#markup' => Link::createFromRoute(
        $this->t('Reset'),
        'content_kanban.kanban'
      )->toString(),
    ];

    return $form;
  }

  /**
   * Get User options
   *
   * @return array
   */
  protected function getUserOptions() {

    $options = array();

    //Load Content Moderation entities
    $content_moderation_entities = $this->kanbanService->getNodeContentModerationEntities($this->formParams['workflow_id']);

    foreach($content_moderation_entities as $content_moderation_entity) {
      $node_nid = $content_moderation_entity->content_entity_id->value;
      if ($node = Node::load($node_nid)) {
        if($user_id = $node->getOwnerId()) {
          if(!array_key_exists($user_id, $options)) {

            //Load user if existing
            if($user = User::load($user_id)) {

              //Add to options
              $options[$user_id] = $user->getAccountName();
            }
          }
        }
      }
    }

    return $options;
  }

  /**
   * Get State options
   *
   * @return array
   */
  protected function getStateOptions() {

    $options = array();

    foreach($this->formParams['states'] as $state_id => $state) {
      $options[$state_id] = $state['label'];
    }

    return $options;
  }

  /**
   * Get User ID filter from request
   *
   * @return int|null
   */
  public static function getUserIDFilter() {

    if(\Drupal::request()->query->has('filter_uid')) {
      return \Drupal::request()->query->getInt('filter_uid');
    }

    return NULL;
  }

  /**
   * Get User ID filter from request
   *
   * @return int|null
   */
  public static function getStateFilter() {

    if(\Drupal::request()->query->has('filter_state')) {
      return \Drupal::request()->query->get('filter_state');
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {


  }

}