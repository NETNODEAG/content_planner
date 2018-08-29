<?php

namespace Drupal\content_planner\Controller;

use Drupal\content_planner\DashboardBlockPluginManager;
use Drupal\content_planner\DashboardService;
use Drupal\content_planner\DashboardSettingsService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Driver\mysql\Connection;

/**
 * Class DashboardController.
 */
class DashboardController extends ControllerBase {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\content_planner\DashboardSettingsService
   */
  protected $dashboardSettingsService;

  /**
   * @var \Drupal\content_planner\DashboardService
   */
  protected $dashboardService;

  /**
   * @var \Drupal\content_planner\DashboardBlockPluginManager
   */
  protected $dashboardBlockPluginManager;

  /**
   * Constructs a new DashboardController object.
   */
  public function __construct(
    Connection $database,
    ConfigFactoryInterface $config_factory,
    DashboardSettingsService $dashboard_settings_service,
    DashboardService $dashboard_service,
    DashboardBlockPluginManager $dashboard_block_plugin_manager
  ) {
    $this->database = $database;
    $this->configFactory = $config_factory;
    $this->dashboardSettingsService = $dashboard_settings_service;
    $this->dashboardService = $dashboard_service;
    $this->dashboardBlockPluginManager = $dashboard_block_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('config.factory'),
      $container->get('content_planner.dashboard_settings_service'),
      $container->get('content_planner.dashboard_service'),
      $container->get('content_planner.dashboard_block_plugin_manager')
    );
  }

  /**
   * Showdashboard.
   *
   * @return array
   */
  public function showDashboard() {

    //Check if Content Calendar or Kanban is enabled
    if(!$this->dashboardService->isContentCalendarEnabled() &&
      !$this->dashboardService->isContentKanbanEnabled()) {

      $this->messenger()->addMessage($this->t('This dashboard can only be used with Content Calendar or Content Kanban enabled'), 'error');
      return array();
    }

    //Get enabled blocks
    $blocks = $this->dashboardSettingsService->getBlockConfigurations();

    //If there are no blocks enabled, display error message
    if(!$blocks) {
      $this->messenger()->addMessage($this->t('Dashboard is not configured yet. Please do this in the Settings tab.'), 'error');
      return array();
    }

    //Get registered Plugins
    $plugins = $this->dashboardBlockPluginManager->getDefinitions();

    //Build blocks
    $block_builds = $this->buildBlocks($blocks, $plugins);

    $build = array(
      '#theme' => 'content_planner_dashboard',
      '#blocks' => $block_builds,
      '#attached' => array(
        'library' => array('content_planner/dashboard')
      ),
    );

    return $build;
  }

  /**
   * Build blocks
   *
   * @param array $blocks
   * @param $plugins
   *
   * @return array
   */
  protected function buildBlocks(array &$blocks, &$plugins) {

    $block_builds = array();

    //Loop over every enabled block
    foreach($blocks as $block_id => $block) {

      //If a Dashboard Block plugin exists
      if(array_key_exists($block_id, $plugins)) {

        /**
         * @var $instance \Drupal\content_planner\DashboardBlockInterface
         */
        $instance = $this->dashboardBlockPluginManager->createInstance($block_id, $block);

        //Build block render array
        if($block_build = $instance->build()) {

          $block_builds[$block_id] = array(
            '#theme' => 'content_planner_dashboard_block',
            '#css_id' => str_replace('_', '-', $block_id),
            '#block_id' => $block_id,
            '#name' => (isset($block['title']) && $block['title']) ? $block['title'] : $instance->getName(),
            '#block' => $block_build,
            '#weight' => $block['weight'],
          );
        }
      }
    }

    return $block_builds;
  }

}
