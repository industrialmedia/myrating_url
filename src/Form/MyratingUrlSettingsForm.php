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
    $form['text_submit'] = [
      '#type' => 'textfield',
      '#title' => 'Текст после успешного голосования',
      '#default_value' => !empty($config->get('text_submit')) ? $config->get('text_submit') : 'Спасибо, ваш голос учтен!',
    ];
    $form['text_chema_org'] = [
      '#type' => 'textarea',
      '#title' => 'Текст микроразметки',
      '#default_value' => !empty($config->get('text_chema_org')) ? $config->get('text_chema_org') : '
        <div itemprop="creativeWorkSeries" itemscope itemtype="http://schema.org/CreativeWorkSeries">
          <div itemprop="name" content="[site:name]. [current-page:title]"></div>
          <div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
              Проголосовало 090911 <span itemprop="reviewCount">%reviewCount</span> 
              оценка <span itemprop="ratingValue">%ratingValue</span> 
              из <span itemprop="bestRating">%bestRating</span>
          </div>
        </div>
        ',
      '#rows' => 15,
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
      ->set('text_submit', $form_state->getValue('text_submit'))
      ->set('text_chema_org', $form_state->getValue('text_chema_org'))
      ->save();
    parent::submitForm($form, $form_state);
  }


}
