<?php

namespace Drupal\myrating_url\Entity;


use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\myrating_url\MyratingUrlInterface;


/**
 * Defines the MyratingUrl entity.
 *
 * @ingroup myrating_url
 *
 * @ContentEntityType(
 *   id = "myrating_url",
 *   label = @Translation("MyratingUrl entity"),
 *   handlers = {
 *     "storage" = "Drupal\myrating_url\MyratingUrlStorage",
 *     "storage_schema" = "Drupal\myrating_url\MyratingUrlStorageSchema",
 *     "list_builder" = "Drupal\myrating_url\MyratingUrlListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\myrating_url\MyratingUrlForm",
 *       "edit" = "Drupal\myrating_url\MyratingUrlForm",
 *       "add_or_edit" = "Drupal\myrating_url\MyratingUrlForm",
 *       "delete" = "Drupal\myrating_url\Form\MyratingUrlDeleteForm",
 *     },
 *     "access" = "Drupal\myrating_url\MyratingUrlAccessControlHandler",
 *   },
 *   fieldable = FALSE,
 *   translatable = FALSE,
 *   base_table = "myrating_url",
 *   data_table = "myrating_url_field_data",
 *   admin_permission = "administer myrating_url entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "source_path",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/seo/myrating_url/{myrating_url}/edit",
 *     "edit-form" = "/admin/seo/myrating_url/{myrating_url}/edit",
 *     "delete-form" = "/admin/seo/myrating_url/{myrating_url}/delete",
 *     "collection" = "/myrating_url/list"
 *   },
 *   field_ui_base_route = "entity.myrating_url.collection",
 * )
 *
 * The 'links':
 * entity.<entity-name>.<link-name>
 * Example: 'entity.myrating_url.canonical'
 *
 *  *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 */
class MyratingUrl extends ContentEntityBase implements MyratingUrlInterface {


  /**
   * {@inheritdoc}
   */
  public function getSourcePath() {
    return $this->get('source_path')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSourcePath($source_path) {
    $this->set('source_path', $source_path);
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);
    $config = \Drupal::config('myrating_url.settings');
    $count_stars = !empty($config->get('count_stars')) ? $config->get('count_stars') : 5;

    $fields['source_path'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Path'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 0,
        'region' => 'hidden',
      ))
      ->setDisplayConfigurable('form', TRUE);


    $fields['rating'] = BaseFieldDefinition::create('fivestar')
      ->setLabel(t('Rating'))
      ->setRequired(TRUE)
      ->setSetting('vote_type', 'vote')
      ->setSetting('rated_while', 'editing') // Оценка при просмотре ИЛИ Оценка при редактировании
      ->setSetting('stars', $count_stars) // Количество звезд
      ->setSetting('allow_clear', 0) // Разрешить пользователям отменять свои оценки
      ->setSetting('allow_revote', 1) // Разрешить пользователям повторно голосовать за уже проголосованный контент
      ->setSetting('allow_ownvote', 1) // Разрешить пользователям голосовать за свой контент
      ->setDisplayOptions('form', array(
        'type' => 'fivestar_stars',
        'weight' => 0,
        'settings' => [
          'display_format' => 'average',
          'fivestar_widget' => 'basic',
          'text_format' => 'none',
        ],
      ))
      ->setDisplayConfigurable('form', TRUE);


    return $fields;

  }
}
