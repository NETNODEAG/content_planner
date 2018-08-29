<?php

namespace Drupal\content_planner;

use Drupal\user\Entity\User;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Class UserProfileImage.
 */
class UserProfileImage {

  public static function generateProfileImageURL(User $user, $image_style) {

    if($user_picture_field = $user->get('user_picture')->getValue()) {

      //Get file entity id
      if($image_file_id = $user_picture_field[0]['target_id']) {

        //Load File entity
        if($file_entity = File::load($image_file_id)) {

          //Load Image Style
          if($style = ImageStyle::load($image_style)) {

            //Build image style url
            return $style->buildUrl($file_entity->getFileUri());
          }

        }

      }

    }

    return FALSE;
  }

}