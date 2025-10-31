<?php

namespace Drupal\scanqr\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'scanqr_widget' widget.
 *
 * @FieldWidget(
 *   id = "scanqr_widget",
 *   label = @Translation("QR Scanner Widget"),
 *   field_types = {
 *     "scanqr_field"
 *   }
 * )
 */
class ScanQRWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'scanner_width' => 400,
      'scanner_height' => 300,
      'enable_manual_input' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['scanner_width'] = [
      '#type' => 'number',
      '#title' => t('Scanner Width'),
      '#default_value' => $this->getSetting('scanner_width'),
      '#min' => 200,
      '#max' => 800,
    ];

    $elements['scanner_height'] = [
      '#type' => 'number',
      '#title' => t('Scanner Height'),
      '#default_value' => $this->getSetting('scanner_height'),
      '#min' => 200,
      '#max' => 600,
    ];

    $elements['enable_manual_input'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable Manual Input'),
      '#description' => t('Allow users to manually enter QR content if scanning fails.'),
      '#default_value' => $this->getSetting('enable_manual_input'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Scanner size: @width x @height px', [
      '@width' => $this->getSetting('scanner_width'),
      '@height' => $this->getSetting('scanner_height'),
    ]);

    if ($this->getSetting('enable_manual_input')) {
      $summary[] = t('Manual input enabled');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $item = $items[$delta];
    
    $element['#attached']['library'][] = 'scanqr/qr-scanner-field';
    
    $field_name = $this->fieldDefinition->getName();
    $wrapper_id = 'scanqr-wrapper-' . $field_name . '-' . $delta;
    
    $element += [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['scanqr-field-widget'],
        'id' => $wrapper_id,
      ],
    ];

    // Scanner container
    $element['scanner_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['scanqr-scanner-container'],
        'data-field-name' => $field_name,
        'data-field-delta' => $delta,
        'data-scanner-width' => $this->getSetting('scanner_width'),
        'data-scanner-height' => $this->getSetting('scanner_height'),
      ],
    ];

    // QR Content field
    $element['qr_content'] = [
      '#type' => 'textarea',
      '#title' => t('Scanned QR Content'),
      '#default_value' => $item->qr_content ?? '',
      '#rows' => 3,
      '#attributes' => [
        'class' => ['scanqr-content-field'],
        'readonly' => !$this->getSetting('enable_manual_input'),
      ],
    ];

    if ($this->getSetting('enable_manual_input')) {
      $element['qr_content']['#description'] = t('This field will be automatically filled when a QR code is scanned, but you can also enter content manually.');
    } else {
      $element['qr_content']['#description'] = t('This field will be automatically filled when a QR code is scanned.');
    }

    // Hidden field for timestamp
    $element['scanned_at'] = [
      '#type' => 'hidden',
      '#default_value' => $item->scanned_at ?? '',
    ];

    // Scanner controls
    $element['controls'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['scanqr-controls']],
    ];

    $element['controls']['start_scanner'] = [
      '#type' => 'button',
      '#value' => t('Start Scanner'),
      '#attributes' => [
        'class' => ['btn-start-scanner'],
        'data-target-field' => $field_name . '-' . $delta . '-qr-content',
        'data-timestamp-field' => $field_name . '-' . $delta . '-scanned-at',
      ],
    ];

    $element['controls']['stop_scanner'] = [
      '#type' => 'button',
      '#value' => t('Stop Scanner'),
      '#attributes' => [
        'class' => ['btn-stop-scanner', 'hidden'],
      ],
    ];

    // Status display
    $element['status'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['scanqr-status']],
    ];

    return $element;
  }

}