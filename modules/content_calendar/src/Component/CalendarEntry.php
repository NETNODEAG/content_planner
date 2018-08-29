<?php

namespace Drupal\content_calendar\Component;

use Drupal\content_calendar\ContentTypeConfigService;
use Drupal\content_calendar\ContentCalendarService;
use Drupal\content_calendar\DateTimeHelper;
use Drupal\content_calendar\Entity\ContentTypeConfig;
use Drupal\content_calendar\Form\SettingsForm;
use Drupal\content_planner\UserProfileImage;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\user\Entity\User;

/**
 * Class CalendarEntry
 *
 * @package Drupal\content_calendar\Component
 */
class CalendarEntry {

  /**
   * @var int
   */
  protected $month;

  /**
   * @var int
   */
  protected $year;

  /**
   * @var \Drupal\content_calendar\Entity\ContentTypeConfig
   */
  protected $contentTypeConfig;

  /**
   * @var \stdClass
   */
  protected $node;

  /**
   * Internal cache for user pictures, used to avoid performance issues
   *
   * @var array
   */
  static $userPictureCache = array();

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * CalendarEntry constructor.
   *
   * @param int $month
   * @param int $year
   * @param \Drupal\content_calendar\Entity\ContentTypeConfig $content_type_config
   * @param \stdClass $node
   */
  public function __construct(
    $month,
    $year,
    ContentTypeConfig $content_type_config,
    \stdClass $node
  ) {
    $this->month = $month;
    $this->year = $year;
    $this->contentTypeConfig = $content_type_config;
    $this->node = $node;

    $this->config = \Drupal::config(SettingsForm::$configName);
  }

  /**
   * Get Node ID
   *
   * @return mixed
   */
  public function getNodeID() {
    return $this->node->nid;
  }

  /**
   * Get the relevant date for the current node.
   *
   * When the Scheduler date is empty, then take the creation date
   *
   * @return int
   */
  public function getRelevantDate() {

    if($this->node->publish_on) {
      return $this->node->publish_on;
    }

    return $this->node->created;
  }

  /**
   * Format creation date as MySQL Date only
   *
   * @return string
   */
  public function formatSchedulingDateAsMySQLDateOnly() {

    $datetime = DateTimeHelper::convertUnixTimestampToDatetime($this->getRelevantDate());

    return $datetime->format(DateTimeHelper::$formatMYSQLDateOnly);
  }

  /**
   * Build
   *
   * @return array
   */
  public function build() {

    //Get User Picture
    $user_picture = $this->getUserPictureURL();

    //Add time to node object
    $this->node->publish_on_time = DateTimeHelper::convertUnixTimestampToDatetime($this->getRelevantDate())->format('H:i');

    //Build options
    $options = $this->buildOptions();

    $build = array(
      '#theme' => 'content_calendar_entry',
      '#node' => $this->node,
      '#node_type_config' => $this->contentTypeConfig,
      '#month' => $this->month,
      '#year' => $this->year,
      '#user_picture' => $user_picture,
      '#options' => $options
    );

    return $build;
  }

  /**
   * Build options before rendering
   *
   * @return array
   */
  protected function buildOptions() {

    $options = array();

    //Background color for unpublished content
    $options['bg_color_unpublished_content'] = ($this->config->get('bg_color_unpublished_content'))
      ? $this->config->get('bg_color_unpublished_content')
      : SettingsForm::$defaultBgColorUnpublishedContent;


    return $options;
  }

  /**
   * Get the URL of the user picture
   *
   * @return bool|string
   */
  protected function getUserPictureURL() {

    //If show user thumb is active
    if($this->config->get('show_user_thumb')) {

      $style_url = FALSE;

      //If a user picture is not in the internal cache, then create one
      if(!array_key_exists($this->node->uid, self::$userPictureCache)) {

        //Load User
        if($user = User::load($this->node->uid)) {
          $style_url = UserProfileImage::generateProfileImageURL($user, 'content_calendar_user_thumb');
        }

        //Store in Cache
        self::$userPictureCache[$this->node->uid] = $style_url;
      }

      return self::$userPictureCache[$this->node->uid];
    }

    return FALSE;
  }

}