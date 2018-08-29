<?php

namespace Drupal\content_kanban\Component;

use Drupal\content_kanban\Form\SettingsForm;
use Drupal\content_kanban\NodeTypeConfig;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\User;

/**
 * Class KanbanEntry
 *
 * @package Drupal\content_kanban\Component
 */
class KanbanEntry {

  /**
   * @var \stdClass
   */
  protected $node;

  /**
   * @var \Drupal\content_kanban\NodeTypeConfig
   */
  protected $nodeTypeConfig;

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
   * KanbanEntry constructor.
   *
   * @param \stdClass $node
   * @param string $content_moderation_status
   * @param \Drupal\content_kanban\NodeTypeConfig $node_type_config
   */
  public function __construct(
    \stdClass $node,
    $content_moderation_status,
    NodeTypeConfig $node_type_config
  ) {
    $this->node = $node;
    $this->contentModerationStatus = $content_moderation_status;
    $this->nodeTypeConfig = $node_type_config;
    $this->config = \Drupal::config(SettingsForm::$configName);
  }

  public function build() {

    //Add time format to Node
    $datetime = new \DateTime();
    $datetime->setTimestamp($this->node->created);
    $this->node->time = $datetime->format('H:i');
    $this->node->entity = Node::load($this->node->nid);

    //Get User Picture
    $user_picture = $this->getUserPictureURL();

    $build = array(
      '#theme' => 'content_kanban_column_entry',
      '#node' => $this->node,
      '#node_type_config' => $this->nodeTypeConfig,
      '#user_picture' => $user_picture
    );

    return $build;
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

          //Get user picture value
          if($user_picture_field = $user->get('user_picture')->getValue()) {

            //Get file entity id
            if($image_file_id = $user_picture_field[0]['target_id']) {

              //Load File entity
              if($file_entity = File::load($image_file_id)) {

                //Load Image Style
                if($style = ImageStyle::load('content_kanban_user_thumb')) {

                  //Build image style url
                  $style_url = $style->buildUrl($file_entity->getFileUri());
                }

              }

            }

          }

        }

        //Store in Cache
        self::$userPictureCache[$this->node->uid] = $style_url;
      }

      return self::$userPictureCache[$this->node->uid];
    }

    return FALSE;
  }

}