<?php

namespace Drupal\scanqr\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ScanQRSettingsForm.
 */
class ScanQRSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'scanqr.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'scanqr_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('scanqr.settings');
    
    $form['scanner_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Scanner Width'),
      '#description' => $this->t('Width of the QR scanner in pixels.'),
      '#default_value' => $config->get('scanner_width') ?: 400,
      '#min' => 200,
      '#max' => 800,
    ];
    
    $form['scanner_height'] = [
      '#type' => 'number',
      '#title' => $this->t('Scanner Height'),
      '#description' => $this->t('Height of the QR scanner in pixels.'),
      '#default_value' => $config->get('scanner_height') ?: 300,
      '#min' => 200,
      '#max' => 600,
    ];
    
    $form['enable_sound'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Sound'),
      '#description' => $this->t('Play a sound when QR code is successfully scanned.'),
      '#default_value' => $config->get('enable_sound') ?: TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('scanqr.settings')
      ->set('scanner_width', $form_state->getValue('scanner_width'))
      ->set('scanner_height', $form_state->getValue('scanner_height'))
      ->set('enable_sound', $form_state->getValue('enable_sound'))
      ->save();
  }

}