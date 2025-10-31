<?php

namespace Drupal\scanqr\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'scanqr_field' field type.
 *
 * @FieldType(
 *   id = "scanqr_field",
 *   label = @Translation("QR Scanner Field"),
 *   description = @Translation("A field that allows scanning QR codes and storing the content."),
 *   default_widget = "scanqr_widget",
 *   default_formatter = "scanqr_formatter"
 * )
 */
class ScanQRField extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['qr_content'] = DataDefinition::create('string')
      ->setLabel(t('QR Content'))
      ->setDescription(t('The content scanned from the QR code.'));

    $properties['scanned_at'] = DataDefinition::create('timestamp')
      ->setLabel(t('Scanned At'))
      ->setDescription(t('Timestamp when the QR code was scanned.'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'qr_content' => [
          'type' => 'text',
          'size' => 'big',
          'not null' => FALSE,
        ],
        'scanned_at' => [
          'type' => 'int',
          'not null' => FALSE,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->get('qr_content')->getValue());
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['qr_content'] = 'https://example.com/sample-qr-content';
    $values['scanned_at'] = \Drupal::time()->getCurrentTime();
    return $values;
  }

}