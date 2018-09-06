<?php

/**
 * @file
 * Contains \Drupal\content_planner\Plugin\DashboardBlock\UserBlock.
 */

namespace Drupal\content_planner\Plugin\DashboardBlock;

use Drupal\content_planner\DashboardBlockBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a user block for Content Planner Dashboard
 *
 * @DashboardBlock(
 *   id = "custom_html_block",
 *   name = @Translation("Custom HTML Widget")
 * )
 */
class CustomHTMLBlock extends CustomHTMLBlockBase {

}