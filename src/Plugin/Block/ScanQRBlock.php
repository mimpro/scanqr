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
      'auto_close' => TRUE,
      'auto_close_delay' => 3,
      'allow_external_redirect' => FALSE,
      'enable_sound' => TRUE,
      'sound_type' => 'beep',
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

    $form['allow_external_redirect'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow external URL redirects'),
      '#default_value' => $config['allow_external_redirect'],
      '#description' => $this->t('WARNING: If unchecked (recommended), only URLs from the current domain will be redirected. External URLs will be blocked for security.'),
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

    $form['auto_close'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Auto-close dialog after scan'),
      '#default_value' => $config['auto_close'],
      '#states' => [
        'visible' => [
          ':input[name="settings[action_type]"]' => ['value' => 'display'],
        ],
      ],
    ];

    $form['auto_close_delay'] = [
      '#type' => 'number',
      '#title' => $this->t('Auto-close delay (seconds)'),
      '#default_value' => $config['auto_close_delay'],
      '#min' => 1,
      '#max' => 30,
      '#description' => $this->t('How many seconds to wait before auto-closing the dialog.'),
      '#states' => [
        'visible' => [
          ':input[name="settings[action_type]"]' => ['value' => 'display'],
          ':input[name="settings[auto_close]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['enable_sound'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable sound notification'),
      '#default_value' => $config['enable_sound'],
      '#description' => $this->t('Play a sound when QR code is detected.'),
    ];

    $form['sound_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Sound type'),
      '#default_value' => $config['sound_type'],
      '#options' => [
        'beep' => $this->t('Beep (High pitch)'),
        'beep-low' => $this->t('Beep (Low pitch)'),
        'success' => $this->t('Success (Rising tone)'),
        'notification' => $this->t('Notification (Two-tone)'),
        'retro' => $this->t('Retro (8-bit style)'),
      ],
      '#description' => $this->t('Choose the sound to play when a QR code is detected.'),
      '#states' => [
        'visible' => [
          ':input[name="settings[enable_sound]"]' => ['checked' => TRUE],
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
    $this->configuration['auto_close'] = $form_state->getValue('auto_close');
    $this->configuration['auto_close_delay'] = $form_state->getValue('auto_close_delay');
    $this->configuration['allow_external_redirect'] = $form_state->getValue('allow_external_redirect');
    $this->configuration['enable_sound'] = $form_state->getValue('enable_sound');
    $this->configuration['sound_type'] = $form_state->getValue('sound_type');
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
      '#auto_close' => $config['auto_close'],
      '#auto_close_delay' => $config['auto_close_delay'],
      '#allow_external_redirect' => $config['allow_external_redirect'],
      '#enable_sound' => $config['enable_sound'],
      '#sound_type' => $config['sound_type'],
      '#attached' => [
        'library' => [
          'scanqr/qr-scanner-block',
          'core/drupal.dialog.ajax',
        ],
      ],
    ];
  }

}
