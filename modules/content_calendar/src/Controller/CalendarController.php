<?php

namespace Drupal\content_calendar\Controller;

use Drupal\content_calendar\Component\Calendar;
use Drupal\content_calendar\ContentCalendarService;
use Drupal\content_calendar\DateTimeHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\content_calendar\ContentTypeConfigService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class CalendarController.
 */
class CalendarController extends ControllerBase {

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Drupal\content_calendar\ContentTypeConfigService definition.
   *
   * @var \Drupal\content_calendar\ContentTypeConfigService
   */
  protected $contentTypeConfigService;

  /**
   * @var \Drupal\content_calendar\ContentCalendarService
   */
  protected $contentCalendarService;

  /**
   * Constructs a new CalendarController object.
   */
  public function __construct(
    RequestStack $request_stack,
    ContentTypeConfigService $content_type_config_service,
    ContentCalendarService $content_calendar_service,
    AccountProxyInterface $current_user
  ) {
    $this->request = $request_stack->getCurrentRequest();
    $this->contentTypeConfigService = $content_type_config_service;
    $this->contentCalendarService = $content_calendar_service;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('content_calendar.content_type_config_service'),
      $container->get('content_calendar.content_calendar_service'),
      $container->get('current_user')
    );
  }

  /**
   * Show Calendar year
   */
  public function showCurrentCalendarYear() {

    $year = date('Y');

    return $this->showCalendarYear($year);
  }

  /**
   * Show Calendar year
   */
  public function showCalendarYear($year) {

    $calendars = array();

    //Get content type config entities
    $content_type_config_entities = $this->contentTypeConfigService->loadAllEntities();

    //Check if Content Calendar has been configured
    if(!$content_type_config_entities) {
      $this->messenger()->addMessage($this->t('Content Calendar is not configured yet. Please do this in the settings tab.'), 'error');
      return array();
    }

    //Generate calendar structures
    foreach(range(1, 12) as $month) {

      //Create new Calendar
      $calender = new Calendar(
        $this->contentTypeConfigService,
        $this->contentCalendarService,
        $month,
        $year,
        $this->currentUser
      );

      $calendars[] = $calender->build();
    }

    //Get Filter Form
    $form_params = array(
      'current_year' => date('Y'),
      'selected_year' => $year,
    );
    $filters_form = \Drupal::formBuilder()->getForm('Drupal\content_calendar\Form\CalenderOverviewFilterForm', $form_params);

    $build = array(
      '#theme' => 'content_calendar_overview',
      '#calendars' => $calendars,
      '#filters_form' => $filters_form,
    );

    return $build;
  }

  /**
   * Update creation date of a given Node
   *
   * @param \Drupal\node\NodeInterface $node
   * @param string $date
   *
   * @return \Zend\Diactoros\Response\JsonResponse
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateNodePublishDate(NodeInterface $node, $date) {

    $data = array(
      'success' => FALSE,
      'message' => NULL,
    );

    //Get content type config entities
    $content_type_config_entities = $this->contentTypeConfigService->loadAllEntities();

    //Check for allowed types, marked in the Content Calendar settings
    if(!array_key_exists($node->getType(), $content_type_config_entities)) {

      $data['message'] = $this->t('Action is not allowed for Nodes of type @type', array('@type' => $node->getType()));
      return new JsonResponse($data);
    }

    //Get publish on timestamp
    $publish_on_timestamp = $node->get('publish_on')->getValue();
    $publish_on_timestamp_value = $publish_on_timestamp[0]['value'];

    //Get the Node's publish ondate and return a datetime object
    $original_publish_datetime = DateTimeHelper::convertUnixTimestampToDatetime($publish_on_timestamp_value);

    //Extract hour, minutes and seconds
    $hour = $original_publish_datetime->format('H');
    $minutes = $original_publish_datetime->format('i');
    $seconds = $original_publish_datetime->format('s');

    //Create a new datetime object from the given date
    $new_publish_datetime = \DateTime::createFromFormat('Y-m-d', $date);

    //Set hour, minutes and seconds
    $new_publish_datetime->setTime($hour, $minutes, $seconds);

    //set created time
    $node->set('publish_on', $new_publish_datetime->getTimestamp());

    //Save
    if($node->save() == SAVED_UPDATED) {
      $data['success'] = TRUE;
      $data['message'] = $this->t('The creation date for Node @id has been updated', array('@id' => $node->id()));
    }

    return new JsonResponse($data);
  }

  /**
   * Redirect to current Calendar
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function redirectToCurrentCalendar() {

    $calendar_id = date('Y-n');

    return $this->redirect('content_calendar.calendar', array(), array('fragment' => $calendar_id));
  }

  /**
   * Redirect and jump to a given Calendar directly
   *
   * @param string $calendar_id
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function redirectToCalendar($year, $month) {

    $fragment = $year . '-' . $month;

    return $this->redirect(
      'content_calendar.calendar',
      array('year' => $year),
      array('fragment' => $fragment)
    );
  }

  /**
   * Duplicate Node
   *
   * @param \Drupal\node\NodeInterface $node
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function duplicateNode(NodeInterface $node) {

    $duplicate = $node->createDuplicate();

    $duplicate->setTitle($duplicate->getTitle() . ' clone');

    $duplicate->save();

    $destination = \Drupal::destination()->get();

    return new RedirectResponse($destination);
  }

}
