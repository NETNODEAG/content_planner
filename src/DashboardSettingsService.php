<?php

namespace Drupal\content_planner;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class DashboardSettingsService.
 */
class DashboardSettingsService {

  /**
   * Config name
   *
   * @var string
   */
  static $configName = 'content_planner.dashboard_settings';

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new DashboardSettingsService object.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * @return \Drupal\Core\Config\ImmutableConfig
   */
  public function getSettings() {
    return $this->configFactory->get(self::$configName);
  }

  /**
   * Get Block configurations
   *
   * @return array
   */
  public function getBlockConfigurations() {

    if($settings = $this->getSettings()) {

      if($block_configurations = $settings->get('blocks')) {
        return $block_configurations;
      }

    }

    return array();
  }

  /**
   * Get the configuration of a specific block
   *
   * @param string $block_id
   *
   * @return array|mixed
   */
  public function getBlockConfiguration($block_id) {

    if($block_configurations = $this->getBlockConfigurations()) {

      if(array_key_exists($block_id, $block_configurations)) {
        return $block_configurations[$block_id];
      }

    }

    return array();
  }

  /**
   * Save configuration of a specific block
   *
   * @param string $block_id
   * @param array $configuration
   *
   * @return bool
   */
  public function saveBlockConfiguration($block_id, $configuration) {

    if($block_configurations = $this->getBlockConfigurations()) {

      if(array_key_exists($block_id, $block_configurations)) {

        $block_configurations[$block_id] = $configuration;

        $this->saveBlockConfigurations($block_configurations);
        return TRUE;
      }

    }

    return FALSE;
  }

  /**
   * Save all block configurations
   *
   * @param array $configuration
   */
  public function saveBlockConfigurations(array $configuration) {

    $this->configFactory->getEditable(self::$configName)
      ->set('blocks', $configuration)
      ->save();
  }

}
