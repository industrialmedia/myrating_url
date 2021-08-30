<?php

namespace Drupal\myrating_url\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;



class MyratingUrlSettingsForm extends ConfigFormBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myrating_url_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['myrating_url.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('myrating_url.settings');
    $form['count_stars'] = [
      '#type' => 'number',
      '#title' => 'Количество звезд',
      '#default_value' => !empty($config->get('count_stars')) ? $config->get('count_stars') : 5,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('myrating_url.settings')
      ->set('count_stars', $form_state->getValue('count_stars'))
      ->save();
    parent::submitForm($form, $form_state);
  }


}
