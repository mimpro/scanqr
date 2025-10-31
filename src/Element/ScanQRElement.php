<?php

namespace Drupal\scanqr\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Textfield;

/**
 * Provides a QR scanner form element.
 *
 * @FormElement("scanqr")
 */
class ScanQRElement extends Textfield {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#size' => 60,
      '#maxlength' => 128,
      '#autocomplete_route_name' => FALSE,
      '#process' => [
        [$class, 'processAutocomplete'],
        [$class, 'processScanQR'],
      ],
      '#pre_render' => [
        [$class, 'preRenderTextfield'],
      ],
      '#theme' => 'input__textfield',
      '#theme_wrappers' => ['form_element'],
      '#scanner_width' => 400,
      '#scanner_height' => 300,
      '#enable_manual_input' => TRUE,
    ];
  }

  /**
   * Process callback for QR scanner element.
   */
  public static function processScanQR(&$element, FormStateInterface $form_state, &$complete_form) {
    // Add QR scanner library
    $element['#attached']['library'][] = 'scanqr/qr-scanner-webform';

    // Add scanner-specific attributes and classes
    $element['#attributes']['class'][] = 'js-webform-scanqr';
    $element['#attributes']['data-scanner-width'] = $element['#scanner_width'];
    $element['#attributes']['data-scanner-height'] = $element['#scanner_height'];
    $element['#attributes']['data-enable-manual-input'] = $element['#enable_manual_input'] ? 'true' : 'false';
    
    // Set placeholder if not set
    if (!isset($element['#attributes']['placeholder'])) {
      $element['#attributes']['placeholder'] = 'Scan QR code or enter manually';
    }
    
    // Make readonly if manual input is disabled
    if (empty($element['#enable_manual_input'])) {
      $element['#attributes']['readonly'] = 'readonly';
    }

    return $element;
  }

}