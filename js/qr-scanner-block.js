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
        var autoClose = $config.data('auto-close') === '1' || $config.data('auto-close') === 1;
        var autoCloseDelay = parseInt($config.data('auto-close-delay')) || 3;
        var allowExternalRedirect = $config.data('allow-external-redirect') === '1' || $config.data('allow-external-redirect') === 1;
        var enableSound = $config.data('enable-sound') === '1' || $config.data('enable-sound') === 1;
        var soundType = $config.data('sound-type') || 'beep';
        
        var stream = null;
        var scanning = false;
        var animationId = null;
        var currentDialog = null;

        // Click handler to open modal
        $button.on('click', function (e) {
          e.preventDefault();
          openScannerModal();
        });

        function openScannerModal() {
          // Create and open Drupal dialog
          var dialogOptions = $button.data('dialog-options') || {};
          var dialogWidth = dialogOptions.width || 500;
          
          var $dialogContent = $modal.clone();
          currentDialog = Drupal.dialog($dialogContent, {
            title: Drupal.t('Scan QR Code'),
            width: dialogWidth,
            dialogClass: 'scanqr-block-dialog',
            buttons: [{
              text: Drupal.t('Close'),
              class: 'button',
              click: function () {
                stopScanning();
                currentDialog.close();
              }
            }],
            close: function () {
              stopScanning();
              currentDialog = null;
            }
          });
          
          currentDialog.showModal();
          
          // Start scanner automatically after modal opens
          setTimeout(function () {
            startScanning($dialogContent);
          }, 300);
        }

        function startScanning($dialogContent) {
          var $dialogVideo = $dialogContent.find('#scanqr-block-video')[0];
          var $dialogCanvas = $dialogContent.find('#scanqr-block-canvas')[0];
          var $dialogStatus = $dialogContent.find('.scanqr-block-status');
          var $dialogResult = $dialogContent.find('.scanqr-block-result');
          var $dialogResultMsg = $dialogContent.find('.scanqr-block-result-message');
          
          if (!$dialogVideo || !$dialogCanvas) {
            return;
          }

          var canvasContext = $dialogCanvas.getContext('2d', { willReadFrequently: true });
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
                if (!scanning) {
                  return;
                }
                
                if ($dialogVideo.readyState !== $dialogVideo.HAVE_ENOUGH_DATA) {
                  animationId = requestAnimationFrame(tick);
                  return;
                }
                
                // Check if video has valid dimensions
                if ($dialogVideo.videoWidth === 0 || $dialogVideo.videoHeight === 0) {
                  animationId = requestAnimationFrame(tick);
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
                  handleScan(code.data, $dialogResult, $dialogResultMsg, $dialogStatus);
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

        function isInternalUrl(url) {
          try {
            // Handle relative URLs (they're always internal)
            if (!url.match(/^https?:\/\//i)) {
              return true;
            }
            
            // Parse the URL
            var urlObj = new URL(url);
            var currentHost = window.location.hostname;
            
            // Check if hostnames match
            return urlObj.hostname === currentHost;
          }
          catch (e) {
            // If URL parsing fails, treat as external for safety
            return false;
          }
        }

        function isUrl(value) {
          // Check if the value looks like a URL
          return /^(https?:\/\/|\/)/i.test(value);
        }

        function playBeep(soundType) {
          try {
            var audioContext = new (window.AudioContext || window.webkitAudioContext)();
            var gainNode = audioContext.createGain();
            gainNode.connect(audioContext.destination);
            gainNode.gain.value = 0.3; // Volume
            
            switch(soundType) {
              case 'beep-low':
                // Low frequency beep
                var oscillator = audioContext.createOscillator();
                oscillator.type = 'sine';
                oscillator.frequency.value = 300;
                oscillator.connect(gainNode);
                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.2);
                oscillator.onended = function() {
                  audioContext.close();
                };
                break;
                
              case 'success':
                // Rising tone
                var oscillator = audioContext.createOscillator();
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(600, audioContext.currentTime);
                oscillator.frequency.linearRampToValueAtTime(900, audioContext.currentTime + 0.15);
                oscillator.connect(gainNode);
                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.15);
                oscillator.onended = function() {
                  audioContext.close();
                };
                break;
                
              case 'notification':
                // Two-tone sequence
                var oscillator1 = audioContext.createOscillator();
                oscillator1.type = 'sine';
                oscillator1.frequency.value = 800;
                oscillator1.connect(gainNode);
                oscillator1.start();
                oscillator1.stop(audioContext.currentTime + 0.1);
                
                var oscillator2 = audioContext.createOscillator();
                oscillator2.type = 'sine';
                oscillator2.frequency.value = 1000;
                oscillator2.connect(gainNode);
                oscillator2.start(audioContext.currentTime + 0.12);
                oscillator2.stop(audioContext.currentTime + 0.22);
                oscillator2.onended = function() {
                  audioContext.close();
                };
                break;
                
              case 'retro':
                // 8-bit style square wave
                var oscillator = audioContext.createOscillator();
                oscillator.type = 'square';
                oscillator.frequency.value = 800;
                oscillator.connect(gainNode);
                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.2);
                oscillator.onended = function() {
                  audioContext.close();
                };
                break;
                
              case 'beep':
              default:
                // Standard beep
                var oscillator = audioContext.createOscillator();
                oscillator.type = 'sine';
                oscillator.frequency.value = 800;
                oscillator.connect(gainNode);
                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.2);
                oscillator.onended = function() {
                  audioContext.close();
                };
                break;
            }
          }
          catch (e) {
            // Audio API not supported, silently fail
            console.log('Audio notification not supported');
          }
        }

        function handleScan(scannedValue, $dialogResult, $dialogResultMsg, $dialogStatus) {
          stopScanning();
          
          // Play beep sound on successful scan if enabled
          if (enableSound) {
            playBeep(soundType);
          }
          
          $dialogStatus.html('<span class="success">' + Drupal.t('QR Code detected!') + '</span>');
          
          // Check if scanned value is a URL and handle auto-redirect
          if (isUrl(scannedValue) && isInternalUrl(scannedValue)) {
            // Auto-redirect to internal URLs
            window.location.href = scannedValue;
            return;
          }
          
          // If it's an external URL and external redirects are not allowed, show warning
          if (isUrl(scannedValue) && !allowExternalRedirect) {
            $dialogStatus.html('<span class="error">' + Drupal.t('External URL detected. Redirects are only allowed to internal pages.') + '</span>');
            $dialogResultMsg.html('<strong>' + Drupal.t('Scanned URL: @url', {'@url': scannedValue}) + '</strong>');
            $dialogResult.show();
            
            if (autoClose) {
              setTimeout(function () {
                if (currentDialog) {
                  currentDialog.close();
                }
              }, autoCloseDelay * 1000);
            }
            return;
          }
          
          // If it's an external URL and external redirects ARE allowed, redirect
          if (isUrl(scannedValue) && allowExternalRedirect) {
            window.location.href = scannedValue;
            return;
          }
          
          if (actionType === 'redirect' && redirectUrl) {
            // Redirect to URL with scanned value
            var targetUrl = redirectUrl.replace('@value', encodeURIComponent(scannedValue));
            
            // Check if the target URL is internal before redirecting (unless external is explicitly allowed)
            if (allowExternalRedirect || isInternalUrl(targetUrl)) {
              window.location.href = targetUrl;
            }
            else {
              // External URL detected - show warning instead of redirecting
              $dialogStatus.html('<span class="error">' + Drupal.t('External URL detected. Redirects are only allowed to internal pages.') + '</span>');
              $dialogResultMsg.html('<strong>' + Drupal.t('Scanned URL: @url', {'@url': targetUrl}) + '</strong>');
              $dialogResult.show();
              
              // Keep dialog open for external URLs
              if (autoClose) {
                setTimeout(function () {
                  if (currentDialog) {
                    currentDialog.close();
                  }
                }, autoCloseDelay * 1000);
              }
            }
          }
          else {
            // Display the scanned value
            var message = displayMessage.replace('@value', scannedValue);
            $dialogResultMsg.html('<strong>' + message + '</strong>');
            $dialogResult.show();
            
            // Auto-close after configured delay if enabled
            if (autoClose) {
              setTimeout(function () {
                if (currentDialog) {
                  currentDialog.close();
                }
              }, autoCloseDelay * 1000);
            }
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
