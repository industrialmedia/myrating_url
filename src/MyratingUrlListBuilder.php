<?php

namespace Drupal\myrating_url;


use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityStorageInterface;


/**
 * Provides a list controller for myrating_url entity.
 *
 * @ingroup myrating_url
 */
class MyratingUrlListBuilder extends EntityListBuilder {


  /**
   * Database Service Object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;


  /**
   * Constructs a new object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Database\Connection $database
   *   Database Service Object.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, Connection $database) {
    parent::__construct($entity_type, $storage);
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('database')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myrating_url_list_builder';
  }


  /**
   * {@inheritdoc}
   */
  public function load() {

    $entity_ids = $this->getEntityIds();

    $votingapi_results = [];
    if ($entity_ids) {
      $result = $this->database->select('votingapi_result', 'v')
        ->fields('v', ['entity_id', 'type', 'function', 'value'])
        ->condition('entity_type', 'myrating_url')
        ->condition('entity_id', $entity_ids, 'IN')
        ->execute();
      while ($row = $result->fetchAssoc()) {
        $votingapi_results[$row['entity_id']][$row['type']][$row['function']] = $row['value'];
      }
    }

    $entities = parent::load();
    foreach ($entities as $entity) {
      $entity_id = $entity->id();
      if (isset($votingapi_results[$entity_id]['vote'])) {
        $entity->_vote = $votingapi_results[$entity_id]['vote'];
      }
    }

    return $entities;

  }


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];
    $header['id'] = $this->t('ID');
    $header['path'] = $this->t('Path');
    $header['vote_count'] = $this->t('Vote count');
    $header['vote_average'] = $this->t('Vote average');
    $header['vote_sum'] = $this->t('Vote sum');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $myrating_url) {
    /* @var $myrating_url \Drupal\myrating_url\Entity\MyratingUrl */
    $row = [];
    // id
    $row['id'] = $myrating_url->id();
    // path
    $source_path = $myrating_url->getSourcePath();
    $url = Url::fromUserInput($source_path);
    $path_link = Link::fromTextAndUrl($source_path, $url);
    $row['path'] = $path_link;
    // vote
    $row['vote_count'] = 0;
    $row['vote_average'] = 0;
    $row['vote_sum'] = 0;
    if (isset($myrating_url->_vote)) {
      $row['vote_count'] = $myrating_url->_vote['vote_count'];
      $row['vote_average'] = $myrating_url->_vote['vote_average'];
      $row['vote_sum'] = $myrating_url->_vote['vote_sum'];
    }
    return $row + parent::buildRow($myrating_url);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();

    $link = Link::createFromRoute('Настройки', 'myrating_url.admin.settings');
    $link = $link->toString();

    $build['help'] = [
      '#markup' => '
        <p>Добавить голоса для конкретной страницы можно только на странице.</p>
        <h3>' . $link . ' рейтинга страниц</h3>',
      '#weight' => -10,
    ];
    $build = [
      'list_rows' => $build,
    ];
    return $build;
  }


}
