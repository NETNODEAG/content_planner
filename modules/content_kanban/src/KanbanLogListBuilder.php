<?php

namespace Drupal\content_kanban;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Kanban Log entities.
 *
 * @ingroup content_kanban
 */
class KanbanLogListBuilder extends EntityListBuilder {

  /**
   * Custom load of entities
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   */
  public function load() {

    $query = \Drupal::entityQuery('content_kanban_log');
    $query->sort('created', 'DESC');

    $result = $query->execute();

    return $this->storage->loadMultiple($result);
  }


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {

    $header['id'] = $this->t('Kanban Log ID');
    $header['name'] = $this->t('Name');
    $header['workflow'] = $this->t('Workflow');
    $header['node'] = $this->t('Node') . ' / ' . $this->t('Node ID');
    $header['state_from'] = $this->t('State from');
    $header['state_to'] = $this->t('State to');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {

    /* @var $entity \Drupal\content_kanban\Entity\KanbanLog */

    $row['id'] = $entity->id();
    $row['name'] = $entity->label();

    //Workflow
    if($workflow = $entity->getWorkflow()) {
      $row['workflow'] = $workflow->label();
    } else {
      $row['workflow'] = t('Workflow with ID @id does not exist anymore', array('@id' => $entity->getWorkflowID()));
    }

    //Node
    if($node = $entity->getNode()) {

      $node_link = Link::createFromRoute($node->getTitle(), 'entity.node.canonical', array('node' => $entity->getNodeID()));

      $row['node'] = $node_link;
    } else {
      $row['node'] = t('Node with ID @id does not exist anymore', array('@id' => $entity->getNodeID()));
    }

    $row['state_from'] = $entity->getStateFrom();
    $row['state_to'] = $entity->getStateTo();

    return $row + parent::buildRow($entity);
  }

}
