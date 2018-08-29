<?php

/**
 * @file
 * Contains \Drupal\content_planner\Annotation\DashboardBlock.
 */

namespace Drupal\content_planner\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a flavor item annotation object.
 *
 * @see \Drupal\content_planner\DashboardBlockPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class DashboardBlock extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the flavor.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $name;

}
