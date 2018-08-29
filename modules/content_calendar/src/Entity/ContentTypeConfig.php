<?php

namespace Drupal\content_calendar\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Content Type Config entity.
 *
 * @ConfigEntityType(
 *   id = "content_type_config",
 *   label = @Translation("Content Type Config"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\content_calendar\ContentTypeConfigListBuilder",
 *     "form" = {
 *       "add" = "Drupal\content_calendar\Form\ContentTypeConfigForm",
 *       "edit" = "Drupal\content_calendar\Form\ContentTypeConfigForm",
 *       "delete" = "Drupal\content_calendar\Form\ContentTypeConfigDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\content_calendar\ContentTypeConfigHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "content_type_config",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/content-calendar/content-type-config/{content_type_config}",
 *     "add-form" = "/admin/content-calendar/content-type-config/add",
 *     "edit-form" = "/admin/content-calendar/content-type-config/{content_type_config}/edit",
 *     "delete-form" = "/admin/content-calendar/content-type-config/{content_type_config}/delete",
 *     "collection" = "/admin/content-calendar/content-type-config"
 *   }
 * )
 */
class ContentTypeConfig extends ConfigEntityBase implements ContentTypeConfigInterface {

  /**
   * The Content Type Config ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Content Type Config label.
   *
   * @var string
   */
  protected $label;

  /**
   * The color value.
   *
   * @var string
   */
  protected $color = NULL;

  /**
   * Get the saved color value
   *
   * @return string
   */
  public function getColor() {

    if($this->color) {
      return $this->color;
    }

    return 'farbe eingeben';
  }

}
