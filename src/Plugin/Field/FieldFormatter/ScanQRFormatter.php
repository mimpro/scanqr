<?php

namespace Drupal\scanqr\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Plugin implementation of the 'scanqr_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "scanqr_formatter",
 *   label = @Translation("QR Content Formatter"),
 *   field_types = {
 *     "scanqr_field"
 *   }
 * )
 */
class ScanQRFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'display_mode' => 'content_only',
      'show_timestamp' => TRUE,
      'generate_qr_code' => FALSE,
      'qr_code_size' => 200,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['display_mode'] = [
      '#type' => 'select',
      '#title' => t('Display Mode'),
      '#options' => [
        'content_only' => t('Content Only'),
        'content_with_qr' => t('Content with QR Code'),
        'qr_only' => t('QR Code Only'),
      ],
      '#default_value' => $this->getSetting('display_mode'),
    ];

    $elements['show_timestamp'] = [
      '#type' => 'checkbox',
      '#title' => t('Show Timestamp'),
      '#description' => t('Display when the QR code was scanned.'),
      '#default_value' => $this->getSetting('show_timestamp'),
    ];

    $elements['generate_qr_code'] = [
      '#type' => 'checkbox',
      '#title' => t('Generate QR Code'),
      '#description' => t('Generate a QR code image from the scanned content.'),
      '#default_value' => $this->getSetting('generate_qr_code'),
    ];

    $elements['qr_code_size'] = [
      '#type' => 'number',
      '#title' => t('QR Code Size'),
      '#description' => t('Size of the generated QR code in pixels.'),
      '#default_value' => $this->getSetting('qr_code_size'),
      '#min' => 100,
      '#max' => 500,
      '#states' => [
        'visible' => [
          ':input[name$="[settings_edit_form][settings][generate_qr_code]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $display_modes = [
      'content_only' => t('Content Only'),
      'content_with_qr' => t('Content with QR Code'),
      'qr_only' => t('QR Code Only'),
    ];

    $summary[] = t('Display mode: @mode', ['@mode' => $display_modes[$this->getSetting('display_mode')]]);

    if ($this->getSetting('show_timestamp')) {
      $summary[] = t('Show timestamp');
    }

    if ($this->getSetting('generate_qr_code')) {
      $summary[] = t('Generate QR code (@size px)', ['@size' => $this->getSetting('qr_code_size')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = $this->viewValue($item);
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return array
   *   The textual output generated as a render array.
   */
  protected function viewValue(FieldItemInterface $item) {
    $qr_content = $item->qr_content;
    $scanned_at = $item->scanned_at;

    if (empty($qr_content)) {
      return ['#markup' => t('No QR content available')];
    }

    $elements = [
      '#type' => 'container',
      '#attributes' => ['class' => ['scanqr-formatter']],
    ];

    $display_mode = $this->getSetting('display_mode');

    // Display content
    if ($display_mode === 'content_only' || $display_mode === 'content_with_qr') {
      $elements['content'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['scanqr-content']],
      ];

      // Check if content is a URL
      if (filter_var($qr_content, FILTER_VALIDATE_URL)) {
        $elements['content']['link'] = [
          '#type' => 'link',
          '#title' => $qr_content,
          '#url' => \Drupal\Core\Url::fromUri($qr_content),
          '#attributes' => [
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
          ],
        ];
      } else {
        $elements['content']['text'] = [
          '#markup' => '<div class="qr-content-text">' . nl2br(htmlspecialchars($qr_content)) . '</div>',
        ];
      }
    }

    // Generate QR code if enabled
    if ($this->getSetting('generate_qr_code') && ($display_mode === 'content_with_qr' || $display_mode === 'qr_only')) {
      try {
        $options = new QROptions([
          'outputType' => QRCode::OUTPUT_MARKUP_SVG,
          'svgViewBoxSize' => $this->getSetting('qr_code_size'),
        ]);

        $qrcode = new QRCode($options);
        $svg_output = $qrcode->render($qr_content);

        $elements['qr_code'] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['scanqr-generated-code']],
        ];

        $elements['qr_code']['svg'] = [
          '#markup' => $svg_output,
        ];
      } catch (\Exception $e) {
        $elements['qr_code_error'] = [
          '#markup' => '<div class="scanqr-error">' . t('Error generating QR code: @error', ['@error' => $e->getMessage()]) . '</div>',
        ];
      }
    }

    // Show timestamp
    if ($this->getSetting('show_timestamp') && !empty($scanned_at)) {
      $elements['timestamp'] = [
        '#markup' => '<div class="scanqr-timestamp">' . 
                     t('Scanned on: @date', ['@date' => \Drupal::service('date.formatter')->format($scanned_at, 'medium')]) . 
                     '</div>',
      ];
    }

    return $elements;
  }

}