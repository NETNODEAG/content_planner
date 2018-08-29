<?php

/**
 * @file
 * Contains content_kanban_log.page.inc.
 *
 * Page callback for Kanban Log entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Kanban Log templates.
 *
 * Default template: content_kanban_log.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_content_kanban_log(array &$variables) {
  // Fetch KanbanLog Entity Object.
  $content_kanban_log = $variables['elements']['#content_kanban_log'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
