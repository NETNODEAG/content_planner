<?php

namespace Drupal\content_calendar\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CalenderOverviewFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'calendar_overview_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $params = array()) {

    //Add Calendar select box
    $this->addCalendarYearSelectBox($form, $form_state, $params);

    $this->addJumpLinks($form, $form_state, $params);

    return $form;
  }

  /**
   * Add Calendar select box
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   * @param $params
   */
  protected function addCalendarYearSelectBox(array &$form, FormStateInterface &$formState, $params) {

    //Date range
    $year_range = range(($params['current_year'] - 3), ($params['current_year'] + 3));

    $years = array_combine($year_range, $year_range);

    $form['calendar_year'] = [
      '#type' => 'select',
      '#options' => $years,
      '#required' => TRUE,
      '#default_value' => $params['selected_year'],
    ];

  }

  protected function addJumpLinks(array &$form, FormStateInterface &$formState, $params) {

    $jump_liks = array();

    $months = array(
      1 => t('Jan'),
      2 => t('Feb'),
      3 => t('Mar'),
      4 => t('Apr'),
      5 => t('Mai'),
      6 => t('Jun'),
      7 => t('Jul'),
      8 => t('Aug'),
      9 => t('Sept'),
      10 => t('Oct'),
      11 => t('Nov'),
      12 => t('Dec'),
    );

    $form['jump_links'] = array(
      '#theme' => 'content_calendar_jump_links',
      '#months' => $months,
      '#year' => $params['selected_year']
    );

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {}

}