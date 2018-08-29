<?php

namespace Drupal\content_calendar\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\scheduler\SchedulerEvent;
use Drupal\scheduler\SchedulerEvents;

class SchedulerPublishSubScriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // The values in the arrays give the function names below.
    $events[SchedulerEvents::PUBLISH][] = ['onNodePublish'];
    return $events;
  }

  /**
   * Act upon a node publish
   *
   * @param \Drupal\scheduler\SchedulerEvent $event
   */
  public function onNodePublish(SchedulerEvent $event) {

    //If the Content Kanban module exists
    if(\Drupal::moduleHandler()->moduleExists('content_kanban')) {

      /** @var \Drupal\node\Entity\Node $node */
      $node = $event->getNode();

      //Set status to published
      $node->setPublished(TRUE);

      //Set Moderation state to published
      $node->moderation_state->value = 'published';

      //Return updated node to event which in turn returns it to the scheduler module
      $event->setNode($node);

    }
  }

}