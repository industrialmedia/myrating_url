<?php

namespace Drupal\myrating_url;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;


class MyratingUrlStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);
    /*
    $schema['myrating_url_field_data']['indexes'] += [
      'myrating_url__source_path__langcode' => ['source_path', 'langcode'],
    ];
    */
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping) {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);
    $field_name = $storage_definition->getName();
    if ($table_name == 'myrating_url') {
      switch ($field_name) {
        case 'source_path':
          $schema['fields'][$field_name]['not null'] = TRUE;
          break;
      }
      switch ($field_name) {
        case 'source_path':
          $this->addSharedTableFieldIndex($storage_definition, $schema, TRUE);
          break;
      }
    }
    return $schema;
  }

}
