<?php

namespace Drupal\scanqr\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Test form for QR scanner element.
 */
class TestScanQRForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'test_scanqr_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#markup' => '<p>' . $this->t('This is a test form to verify the QR scanner element is working correctly.') . '</p>',
    ];

    $form['qr_scanner'] = [
      '#type' => 'scanqr',
      '#title' => $this->t('QR Scanner Test'),
      '#description' => $this->t('Use the scanner below or enter text manually.'),
      '#scanner_width' => 400,
      '#scanner_height' => 300,
      '#enable_manual_input' => TRUE,
      '#required' => FALSE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $qr_value = $form_state->getValue('qr_scanner');
    
    $this->messenger()->addStatus($this->t('QR Scanner value: @value', ['@value' => $qr_value ?: '(empty)']));
  }

}