<?php

/**
 * @file
 * Provides Drupal\content_planner\DashboardBlockInterface
 */

namespace Drupal\content_planner;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines an interface for dashboard block plugins.
 */
interface DashboardBlockInterface extends PluginInspectionInterface {

  /**
   * Return the name of the block.
   *
   * @return string
   */
  public function getName();

  /**
   * Check if the plugin is configurable
   *
   * @return bool
   */
  public function isConfigurable();


  /**
   * Get Configuration passed in by Plugin Manager
   *
   * @return array
   */
  public function getConfiguration();

  /**
   * Build the block and return a renderable array
   *
   * @return array
   */
  public function build();

  /**
   * Add additonal form elements specific to the Plugin
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param array $block_configuration
   *
   * @return mixed
   */
  public function getConfigSpecificFormFields(FormStateInterface &$form_state, Request &$request, array $block_configuration);

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function validateForm(array &$form, FormStateInterface &$form_state);

  /**
   * Submit form handler
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function submitSettingsForm(array &$form, FormStateInterface &$form_state);
}
