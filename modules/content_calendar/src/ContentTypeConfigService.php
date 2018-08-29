<?php

namespace Drupal\content_calendar;

use Drupal\content_calendar\Entity\ContentTypeConfig;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class ContentTypeConfigService.
 */
class ContentTypeConfigService {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new ContentTypeConfigService object.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Load all config entities
   *
   * @return \Drupal\content_calendar\Entity\ContentTypeConfig[]
   */
  public function loadAllEntities() {
    return ContentTypeConfig::loadMultiple();
  }

  /**
   * Load config entity by Content Type
   *
   * @param string $content_type
   *
   * @return bool|\Drupal\content_calendar\Entity\ContentTypeConfig|null|static
   */
  public function loadEntityByContentType($content_type) {

    if($entity = ContentTypeConfig::load($content_type)) {
      return $entity;
    }

    return FALSE;
  }

  /**
   * Create new config entity
   *
   * @param string $node_type
   * @param string $label
   * @param string $color
   *
   * @return int
   */
  public function createEntity($node_type, $label, $color = '#0074bd') {

    $entity_build = array(
      'id' => $node_type,
      'label' => $label,
      'color' => $color,
    );

    $entity = ContentTypeConfig::create($entity_build);

    return $entity->save();
  }

}
