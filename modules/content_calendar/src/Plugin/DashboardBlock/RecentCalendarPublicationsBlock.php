<?php

/**
 * @file
 * Contains \Drupal\content_planner\Plugin\DashboardBlock\UserBlock.
 */

namespace Drupal\content_calendar\Plugin\DashboardBlock;

use Drupal\content_calendar\ContentService;
use Drupal\content_planner\DashboardBlockBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * Provides a block with recent calendar publications for Content Planner Dashboard
 *
 * @DashboardBlock(
 *   id = "recent_calendar_content_block",
 *   name = @Translation("Recent Calendar Publications Widget")
 * )
 */
class RecentCalendarPublicationsBlock extends DashboardBlockBase {

  public function build() {

    $config = $this->getConfiguration();

    $contentService = new ContentService();

    // Last publications
    $last_publications_limit_config = $this->getCustomConfigByKey($config, 'last_publications_limit', 3);
    $last_nodes = $contentService->getRecentContent($last_publications_limit_config);

    // Next publications
    $next_publications_limit_config = $this->getCustomConfigByKey($config, 'next_publications_limit', 3);
    $next_nodes = $contentService->getFollowingContent($next_publications_limit_config);

    return [
      '#theme' => 'recent_calendar_content',
      '#last_nodes' => $last_nodes,
      '#next_nodes' => $next_nodes,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigSpecificFormFields(FormStateInterface &$form_state,
                                              Request &$request,
                                              array $block_configuration) {
    $form = array();

    // Last publications limit
    $last_publications_limit_default_value = $this->getCustomConfigByKey($block_configuration, 'last_publications_limit', 3);

    $form['last_publications_limit'] = array(
      '#type' => 'number',
      '#title' => t('Limit number of recently published nodes'),
      '#required' => FALSE,
      '#default_value' => $last_publications_limit_default_value,
    );

    // Next publications limit
    $next_publications_limit_default_value = $this->getCustomConfigByKey($block_configuration, 'next_publications_limit', 3);

    $form['next_publications_limit'] = array(
      '#type' => 'number',
      '#title' => t('Limit number of nodes to be published'),
      '#required' => FALSE,
      '#default_value' => $next_publications_limit_default_value,
    );

    return $form;
  }
}
