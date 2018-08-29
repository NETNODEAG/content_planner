<?php
/**
 * @file
 * Contains DashboardBlockPluginManager.
 */

namespace Drupal\content_planner;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Dashboard Block plugin manager.
 */
class DashboardBlockPluginManager extends DefaultPluginManager {

  /**
   * Constructs an DashboardBlockPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler
  ) {

    parent::__construct(
      'Plugin/DashboardBlock',
      $namespaces,
      $module_handler,
      'Drupal\content_planner\DashboardBlockInterface',
      'Drupal\content_planner\Annotation\DashboardBlock'
    );

    $this->alterInfo('dashboard_block_info');
    $this->setCacheBackend($cache_backend, 'dashboard_blocks');
  }
}
