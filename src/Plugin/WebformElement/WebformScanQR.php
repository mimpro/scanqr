<?php

namespace Drupal\scanqr\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElementBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'scanqr' element.
 *
 * @WebformElement(
 *   id = "scanqr",
 *   label = @Translation("QR Scanner"),
 *   description = @Translation("Provides QR code scanning functionality in webforms."),
 *   category = @Translation("Advanced elements"),
 * )
 */
class WebformScanQR extends WebformElementBase {

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    return [
      'scanner_width' => 400,
      'scanner_height' => 300,
      'enable_manual_input' => TRUE,
      'placeholder' => 'Scan QR code or enter manually',
    ] + parent::defineDefaultProperties();
  }

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultBaseProperties() {
    $properties = parent::defineDefaultBaseProperties();
    // Remove properties that don't apply to QR scanner
    unset($properties['multiple']);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'title' => '',
      'description' => '',
      'scanner_width' => 400,
      'scanner_height' => 300,
      'enable_manual_input' => TRUE,
      'placeholder' => 'Scan QR code or enter manually',
      'required' => FALSE,
    ] + parent::getDefaultProperties();
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['scanqr'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('QR Scanner settings'),
    ];

    $form['scanqr']['scanner_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Scanner width'),
      '#description' => $this->t('Width of the QR scanner in pixels.'),
      '#min' => 200,
      '#max' => 800,
    ];

    $form['scanqr']['scanner_height'] = [
      '#type' => 'number',
      '#title' => $this->t('Scanner height'),
      '#description' => $this->t('Height of the QR scanner in pixels.'),
      '#min' => 200,
      '#max' => 600,
    ];

    $form['scanqr']['enable_manual_input'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable manual input'),
      '#description' => $this->t('Allow users to manually enter QR content if scanning fails.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, WebformSubmissionInterface $webform_submission = NULL) {
    parent::prepare($element, $webform_submission);

    // Set element type to our custom scanqr element
    $element['#type'] = 'scanqr';
    
    // Pass through scanner settings
    $element['#scanner_width'] = $element['#scanner_width'] ?? 400;
    $element['#scanner_height'] = $element['#scanner_height'] ?? 300;
    $element['#enable_manual_input'] = $element['#enable_manual_input'] ?? TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function formatHtmlItem(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    $value = $this->getValue($element, $webform_submission, $options);

    if (empty($value)) {
      return '';
    }

    // Check if it's a URL
    if (filter_var($value, FILTER_VALIDATE_URL)) {
      return [
        '#type' => 'link',
        '#title' => $value,
        '#url' => \Drupal\Core\Url::fromUri($value),
        '#attributes' => ['target' => '_blank'],
      ];
    }

    return [
      '#markup' => nl2br(htmlspecialchars($value)),
    ];
  }

}