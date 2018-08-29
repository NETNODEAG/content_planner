<?php

namespace Drupal\content_calendar\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ContentTypeConfigForm.
 */
class ContentTypeConfigForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $config_entity = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $config_entity->label(),
      '#description' => $this->t("Label for the Content Type Config."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $config_entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\content_calendar\Entity\ContentTypeConfig::load',
      ],
      '#disabled' => !$config_entity->isNew(),
    ];

    $form['color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Color'),
      '#maxlength' => 20,
      '#default_value' => $config_entity->getColor(),
      '#description' => $this->t("The color value to use for this Content Type inside the Content Calendar. Examples: #ffcc00 or 'red'."),
      '#required' => TRUE,
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $config_entity = $this->entity;

    $status = $config_entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Content Type Config.', [
          '%label' => $config_entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Content Type Config.', [
          '%label' => $config_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($config_entity->toUrl('collection'));
  }

}
