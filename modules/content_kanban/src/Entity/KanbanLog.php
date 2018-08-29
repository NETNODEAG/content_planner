<?php

namespace Drupal\content_kanban\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Kanban Log entity.
 *
 * @ingroup content_kanban
 *
 * @ContentEntityType(
 *   id = "content_kanban_log",
 *   label = @Translation("Kanban Log"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\content_kanban\KanbanLogListBuilder",
 *     "views_data" = "Drupal\content_kanban\Entity\KanbanLogViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\content_kanban\Form\KanbanLogForm",
 *       "add" = "Drupal\content_kanban\Form\KanbanLogForm",
 *       "edit" = "Drupal\content_kanban\Form\KanbanLogForm",
 *       "delete" = "Drupal\content_kanban\Form\KanbanLogDeleteForm",
 *     },
 *     "access" = "Drupal\content_kanban\KanbanLogAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\content_kanban\KanbanLogHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "content_kanban_log",
 *   admin_permission = "administer kanban log entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/content-kanban/logs/{content_kanban_log}",
 *     "add-form" = "/admin/content-kanban/logs/add",
 *     "edit-form" = "/admin/content-kanban/logs/{content_kanban_log}/edit",
 *     "delete-form" = "/admin/content-kanban/logs/{content_kanban_log}/delete",
 *     "collection" = "/admin/content-kanban/logs",
 *   },
 *   field_ui_base_route = "content_kanban_log.settings"
 * )
 */
class KanbanLog extends ContentEntityBase implements KanbanLogInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * Get the from state
   *
   * @return mixed
   */
  public function getStateFrom() {
    return $this->get('state_from')->value;
  }

  /**
   * Get the to state
   *
   * @return mixed
   */
  public function getStateTo() {
    return $this->get('state_to')->value;
  }

  /**
   * Get Node
   *
   * @return \Drupal\node\Entity\Node
   */
  public function getNode() {
    return $this->get('nid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeID() {
    return $this->get('nid')->target_id;
  }

  /**
   * Get Workflow
   *
   * @return \Drupal\workflows\Entity\Workflow
   */
  public function getWorkflow() {
    return $this->get('workflow_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkflowID() {
    return $this->get('workflow_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    //Get base fields
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Kanban Log entity.'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE)
    ;

    //Node ID
    $fields['nid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Node ID'))
      ->setDescription(t('The ID of the Node this Log refers to'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setRequired(TRUE)
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
    ;

    //User ID
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID of author of the Kanban Log entity.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setRequired(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
    ;

    $fields['workflow_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Workflow'))
      ->setDescription(t('The Workflow being used.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setRequired(TRUE)
      ->setSetting('target_type', 'workflow')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
    ;

    $fields['state_from'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Previous State'))
      ->setDescription(t('The id of the previous Workflow State'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE)
    ;

    $fields['state_to'] = BaseFieldDefinition::create('string')
      ->setLabel(t('State To'))
      ->setDescription(t('The id of the current Workflow State'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE)
    ;

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Kanban Log is published.'))
      ->setDefaultValue(TRUE)
//      ->setDisplayOptions('form', [
//        'type' => 'boolean_checkbox',
//        'weight' => 0,
//      ])
    ;

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
    ;

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'))
    ;

    return $fields;
  }

}
