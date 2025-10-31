(function ($, Drupal, drupalSettings, once) {
  'use strict';

  Drupal.behaviors.scanqrBlock = {
    attach: function (context, settings) {
      var buttons = once('scanqr-block', '.scanqr-block-trigger', context);
      buttons.forEach(function (button) {
        var $button = $(button);
        var $wrapper = $button.closest('.scanqr-block-wrapper');
        var $modal = $wrapper.find('#scanqr-block-modal');
        var $video = $('#scanqr-block-video');
        var $canvas = $('#scanqr-block-canvas');
        var $status = $wrapper.find('.scanqr-block-status');
        var $result = $wrapper.find('.scanqr-block-result');
        var $resultMessage = $wrapper.find('.scanqr-block-result-message');
        var $config = $wrapper.find('.scanqr-block-config');
        
        var actionType = $config.data('action-type');
        var redirectUrl = $config.data('redirect-url');
        var displayMessage = $config.data('display-message');
        
        var stream = null;
        var scanning = false;
        var animationId = null;

        // Click handler to open modal
        $button.on('click', function (e) {
          e.preventDefault();
          openScannerModal();
        });

        function openScannerModal() {
          // Create and open Drupal dialog
          var $dialog = Drupal.dialog($modal.clone(), {
            title: Drupal.t('Scan QR Code'),
            width: parseInt($button.data('dialog-options') ? JSON.parse($button.data('dialog-options')).width : 500),
            dialogClass: 'scanqr-block-dialog',
            buttons: [{
              text: Drupal.t('Close'),
              class: 'button',
              click: function () {
                stopScanning();
                $dialog.close();
              }
            }],
            close: function () {
              stopScanning();
            }
          });
          
          $dialog.showModal();
          
          // Start scanner automatically after modal opens
          setTimeout(function () {
            startScanning($dialog);
          }, 300);
        }

        function startScanning($dialog) {
          var $dialogVideo = $dialog.find('#scanqr-block-video')[0];
          var $dialogCanvas = $dialog.find('#scanqr-block-canvas')[0];
          var $dialogStatus = $dialog.find('.scanqr-block-status');
          var $dialogResult = $dialog.find('.scanqr-block-result');
          var $dialogResultMsg = $dialog.find('.scanqr-block-result-message');
          
          if (!$dialogVideo || !$dialogCanvas) {
            return;
          }

          var canvasContext = $dialogCanvas.getContext('2d');
          var constraints = {
            video: {
              facingMode: 'environment',
              width: { ideal: 640 },
              height: { ideal: 480 }
            }
          };

          navigator.mediaDevices.getUserMedia(constraints)
            .then(function (mediaStream) {
              stream = mediaStream;
              $dialogVideo.srcObject = stream;
              $dialogVideo.play();
              scanning = true;
              $dialogStatus.text(Drupal.t('Scanner active - point camera at QR code'));

              function tick() {
                if (!scanning || !$dialogVideo.readyState === $dialogVideo.HAVE_ENOUGH_DATA) {
                  if (scanning) {
                    animationId = requestAnimationFrame(tick);
                  }
                  return;
                }

                $dialogCanvas.width = $dialogVideo.videoWidth;
                $dialogCanvas.height = $dialogVideo.videoHeight;
                canvasContext.drawImage($dialogVideo, 0, 0, $dialogCanvas.width, $dialogCanvas.height);
                
                var imageData = canvasContext.getImageData(0, 0, $dialogCanvas.width, $dialogCanvas.height);
                var code = jsQR(imageData.data, imageData.width, imageData.height, {
                  inversionAttempts: 'dontInvert'
                });

                if (code && code.data) {
                  // QR code detected!
                  handleScan(code.data, $dialog, $dialogResult, $dialogResultMsg, $dialogStatus);
                  return;
                }

                animationId = requestAnimationFrame(tick);
              }

              tick();
            })
            .catch(function (err) {
              $dialogStatus.html('<span class="error">' + Drupal.t('Camera access denied or not available: @error', {'@error': err.message}) + '</span>');
            });
        }

        function handleScan(scannedValue, $dialog, $dialogResult, $dialogResultMsg, $dialogStatus) {
          stopScanning();
          $dialogStatus.html('<span class="success">' + Drupal.t('QR Code detected!') + '</span>');
          
          if (actionType === 'redirect' && redirectUrl) {
            // Redirect to URL with scanned value
            var targetUrl = redirectUrl.replace('@value', encodeURIComponent(scannedValue));
            window.location.href = targetUrl;
          }
          else {
            // Display the scanned value
            var message = displayMessage.replace('@value', scannedValue);
            $dialogResultMsg.html('<strong>' + message + '</strong>');
            $dialogResult.show();
            
            // Auto-close after 3 seconds
            setTimeout(function () {
              $dialog.close();
            }, 3000);
          }
        }

        function stopScanning() {
          scanning = false;
          if (animationId) {
            cancelAnimationFrame(animationId);
            animationId = null;
          }
          if (stream) {
            stream.getTracks().forEach(function (track) {
              track.stop();
            });
            stream = null;
          }
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings, once);
