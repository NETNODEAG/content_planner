<?php

namespace Drupal\content_planner;

/**
 * Class DashboardService.
 */
class DashboardService {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $dashboardSettingsService;

  /**
   * Constructs a new DashboardService object.
   */
  public function __construct(DashboardSettingsService $dashboard_settings_service) {
    $this->dashboardSettingsService = $dashboard_settings_service;
  }

  /**
   * @return \Drupal\Core\Config\ImmutableConfig
   */
  public function getDashboardSettings() {
    return $this->dashboardSettingsService->getSettings();
  }

  /**
   * Check if the Content Calendar is enabled
   *
   * @return bool
   */
  public function isContentCalendarEnabled() {
    return \Drupal::moduleHandler()->moduleExists('content_calendar');
  }

  /**
   * Check if the Content Kanban is enabled
   *
   * @return bool
   */
  public function isContentKanbanEnabled() {
    return \Drupal::moduleHandler()->moduleExists('content_kanban');
  }

}
