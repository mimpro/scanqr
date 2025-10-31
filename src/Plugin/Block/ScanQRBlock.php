<?php

namespace Drupal\scanqr\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a 'Scan QR Code' Block.
 *
 * @Block(
 *   id = "scanqr_block",
 *   admin_label = @Translation("QR Scanner Block"),
 *   category = @Translation("Scan QR"),
 * )
 */
class ScanQRBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'button_text' => $this->t('SCAN'),
      'scanner_width' => 400,
      'scanner_height' => 400,
      'action_type' => 'display',
      'redirect_url' => '',
      'display_message' => $this->t('Scanned value: @value'),
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->configuration;

    $form['button_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button text'),
      '#default_value' => $config['button_text'],
      '#required' => TRUE,
    ];

    $form['scanner_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Scanner width'),
      '#default_value' => $config['scanner_width'],
      '#min' => 200,
      '#max' => 800,
    ];

    $form['scanner_height'] = [
      '#type' => 'number',
      '#title' => $this->t('Scanner height'),
      '#default_value' => $config['scanner_height'],
      '#min' => 200,
      '#max' => 800,
    ];

    $form['action_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Action after scan'),
      '#options' => [
        'display' => $this->t('Display the scanned value'),
        'redirect' => $this->t('Redirect to URL with scanned value'),
      ],
      '#default_value' => $config['action_type'],
    ];

    $form['redirect_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Redirect URL pattern'),
      '#default_value' => $config['redirect_url'],
      '#description' => $this->t('Use @value as placeholder for scanned value. Example: /node/@value or /search?q=@value'),
      '#states' => [
        'visible' => [
          ':input[name="settings[action_type]"]' => ['value' => 'redirect'],
        ],
      ],
    ];

    $form['display_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Display message'),
      '#default_value' => $config['display_message'],
      '#description' => $this->t('Use @value as placeholder for scanned value.'),
      '#states' => [
        'visible' => [
          ':input[name="settings[action_type]"]' => ['value' => 'display'],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['button_text'] = $form_state->getValue('button_text');
    $this->configuration['scanner_width'] = $form_state->getValue('scanner_width');
    $this->configuration['scanner_height'] = $form_state->getValue('scanner_height');
    $this->configuration['action_type'] = $form_state->getValue('action_type');
    $this->configuration['redirect_url'] = $form_state->getValue('redirect_url');
    $this->configuration['display_message'] = $form_state->getValue('display_message');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configuration;

    return [
      '#theme' => 'scanqr_block',
      '#button_text' => $config['button_text'],
      '#scanner_width' => $config['scanner_width'],
      '#scanner_height' => $config['scanner_height'],
      '#action_type' => $config['action_type'],
      '#redirect_url' => $config['redirect_url'],
      '#display_message' => $config['display_message'],
      '#attached' => [
        'library' => [
          'scanqr/qr-scanner-block',
          'core/drupal.dialog.ajax',
        ],
      ],
    ];
  }

}
