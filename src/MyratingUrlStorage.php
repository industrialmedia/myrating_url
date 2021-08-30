<?php

namespace Drupal\myrating_url;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\myrating_url\Entity\MyratingUrl;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;


class MyratingUrlStorage extends SqlContentEntityStorage implements MyratingUrlStorageInterface {


  /**
   * The current path stack.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;


  /**
   * The entity form builder service.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;


  /**
   * Constructs a new CommerceContentEntityStorage object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to be used.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface|null $memory_cache
   *   The memory cache backend to be used.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path stack.
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   The entity form builder service.
   */
  public function __construct(EntityTypeInterface $entity_type, Connection $database, EntityFieldManagerInterface $entity_field_manager, CacheBackendInterface $cache, LanguageManagerInterface $language_manager, MemoryCacheInterface $memory_cache = NULL, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, EntityTypeManagerInterface $entity_type_manager = NULL, CurrentPathStack $current_path, EntityFormBuilderInterface $entity_form_builder) {
    parent::__construct($entity_type, $database, $entity_field_manager, $cache, $language_manager, $memory_cache, $entity_type_bundle_info, $entity_type_manager);
    $this->currentPath = $current_path;
    $this->entityFormBuilder = $entity_form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $database = $container->get('database');
    $entity_field_manager = $container->get('entity_field.manager');
    $cache = $container->get('cache.entity');
    $language_manager = $container->get('language_manager');
    $memory_cache = $container->get('entity.memory_cache');
    $entity_type_bundle_info = $container->get('entity_type.bundle.info');
    $entity_type_manager = $container->get('entity_type.manager');
    $current_path = $container->get('path.current');
    $entity_form_builder = $container->get('entity.form_builder');
    return new static(
      $entity_type,
      $database,
      $entity_field_manager,
      $cache,
      $language_manager,
      $memory_cache,
      $entity_type_bundle_info,
      $entity_type_manager,
      $current_path,
      $entity_form_builder
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getMyratingUrlBySourcePath($source_path = NULL) {
    if (empty($source_path)) {
      $source_path = $this->currentPath->getPath();
    }
    $myrating_urls = $this->loadByProperties([
      'source_path' => $source_path,
    ]);
    if ($myrating_url = reset($myrating_urls)) {
      return $myrating_url;
    }
    return NULL;
  }


  /**
   * {@inheritdoc}
   */
  public function getMyratingUrlForm($path = NULL, $text_submit = '') {
    if (empty($path)) {
      $path = $this->currentPath->getPath();
    }
    $myrating_url = $this->getMyratingUrlBySourcePath($path);
    if (!$myrating_url) {
      $myrating_url = MyratingUrl::create([]);
      $myrating_url->setSourcePath($path);
    }
    $myrating_url_form = $this->entityFormBuilder->getForm($myrating_url, 'add_or_edit', [
      'text_submit' => $text_submit,
    ]);
    return $myrating_url_form;
  }


}
