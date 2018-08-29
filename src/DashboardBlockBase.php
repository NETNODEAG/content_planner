<?php
/**
 * @file
 * Provides Drupal\content_planner\DashboardBlockBase.
 */

namespace Drupal\content_planner;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\ConfigFormBaseTrait;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

class DashboardBlockBase extends PluginBase implements DashboardBlockInterface {

  use ConfigFormBaseTrait;

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {

  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['name'];
  }

  /**
   * {@inheritdoc}
   */
  public function isConfigurable() {

    if(array_key_exists('configurable', $this->pluginDefinition)) {
      return $this->pluginDefinition['configurable'];
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Get basic configuration structure for the block configuration
   *
   * @return array
   */
  public static function getBasicConfigStructure() {

    return array(
      'plugin_id' => NULL,
      'title' => NULL,
      'weight' => 0,
      'configured' => FALSE,
      'plugin_specific_config' => array(),
    );
  }

  /**
   * Get custom config
   *
   * @param array $block_configuration
   * @param string $key
   * @param mixed $default_value The default value to return if key does not exist in the specific configuration
   *
   * @return null
   */
  protected function getCustomConfigByKey(array $block_configuration, $key, $default_value = NULL) {

    //If a given key exists in the plugin specific configuration, then return it
    if((array_key_exists($key, $block_configuration['plugin_specific_config']))) {
      return $block_configuration['plugin_specific_config'][$key];
    }

    return $default_value;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigSpecificFormFields(FormStateInterface &$form_state, Request &$request, array $block_configuration) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface &$form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitSettingsForm(array &$form, FormStateInterface &$form_state) {}

}
