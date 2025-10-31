<?php

namespace Drupal\scanqr\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class ScanQRController.
 */
class ScanQRController extends ControllerBase {

  /**
   * Scanner page.
   *
   * @return array
   *   Render array for the scanner page.
   */
  public function scanner() {
    $build = [];
    
    $build['#attached']['library'][] = 'scanqr/qr-scanner';
    
    $build['intro'] = [
      '#markup' => '<div class="scanqr-page-intro">
        <h2>' . $this->t('QR Code Scanner Demo') . '</h2>
        <p>' . $this->t('This page demonstrates the standalone QR scanner. For content management, use the QR Scanner Field when creating or editing content.') . '</p>
      </div>',
    ];
    
    $build['field_info'] = [
      '#type' => 'details',
      '#title' => $this->t('Using QR Scanner Fields'),
      '#open' => TRUE,
    ];
    
    $build['field_info']['content'] = [
      '#markup' => '<div class="scanqr-field-instructions">
        <h4>' . $this->t('Adding QR Scanner Fields to Content Types') . '</h4>
        <ol>
          <li>' . $this->t('Go to Administration > Structure > Content types') . '</li>
          <li>' . $this->t('Select "Manage fields" for your content type') . '</li>
          <li>' . $this->t('Click "Add field" and select "QR Scanner Field"') . '</li>
          <li>' . $this->t('Configure the field settings as needed') . '</li>
          <li>' . $this->t('Save and use the field when creating content') . '</li>
        </ol>
        
        <h4>' . $this->t('Field Features') . '</h4>
        <ul>
          <li>' . $this->t('Real-time QR code scanning using device camera') . '</li>
          <li>' . $this->t('Automatic timestamp recording') . '</li>
          <li>' . $this->t('Optional manual input fallback') . '</li>
          <li>' . $this->t('Configurable scanner dimensions') . '</li>
          <li>' . $this->t('Multiple display formatters') . '</li>
          <li>' . $this->t('QR code generation from scanned content') . '</li>
        </ul>
      </div>',
    ];
    
    $build['scanner'] = [
      '#theme' => 'scanqr_scanner',
      '#scanner_id' => 'qr-scanner',
      '#width' => 400,
      '#height' => 300,
    ];
    
    $build['instructions'] = [
      '#markup' => '<div class="scanqr-instructions">
        <h3>' . $this->t('Standalone QR Scanner') . '</h3>
        <p>' . $this->t('Point your camera at a QR code to scan it. This is a demo version - use QR Scanner Fields in your content types for full functionality.') . '</p>
        <div id="qr-result" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc; display: none;">
          <strong>Scanned Result:</strong> <span id="qr-result-text"></span>
        </div>
      </div>',
    ];
    
    return $build;
  }

}