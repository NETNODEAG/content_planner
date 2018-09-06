<?php

/**
 * @file
 * Contains \Drupal\content_planner\Plugin\DashboardBlock\UserBlock.
 */

namespace Drupal\content_planner\Plugin\DashboardBlock;

use Drupal\content_planner\DashboardBlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;
use Symfony\Component\HttpFoundation\Request;


/**
 * Provides a view block for Content Planner Dashboard
 *
 * @DashboardBlock(
 *   id = "view_8_block",
 *   name = @Translation("Views Widget 8")
 * )
 */
class View8Block extends ViewBlockBase {

  protected $blockID = 'view_8';
}