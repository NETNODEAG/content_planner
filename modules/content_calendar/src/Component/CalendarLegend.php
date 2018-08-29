<?php

namespace Drupal\content_calendar\Component;

abstract class CalendarLegend {

  /**
   * @param \Drupal\content_calendar\Entity\ContentTypeConfig[] $content_config_entities
   *
   * @return array
   */
  public static function build(array $content_config_entities) {

    $build = array(
      '#theme' => 'content_calendar_legend',
      '#content_type_configs' => $content_config_entities,
    );

    return $build;

  }

}