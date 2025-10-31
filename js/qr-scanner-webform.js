(function ($, Drupal, once) {
  'use strict';

  /**
   * QR Scanner Webform element behavior.
   */
  Drupal.behaviors.scanQRWebform = {
    attach: function (context, settings) {
      $('.js-webform-scanqr', context).each(function() {
        var $input = $(this);
        
        // Check if already processed
        if ($input.hasClass('scanqr-webform-processed')) {
          return;
        }
        $input.addClass('scanqr-webform-processed');
        
        var elementId = $input.attr('id') || 'scanqr-webform-' + Math.random().toString(36).substr(2, 9);
        var scannerWidth = $input.data('scanner-width') || 400;
        var scannerHeight = $input.data('scanner-height') || 300;
        var enableManualInput = $input.data('enable-manual-input') !== false;
        
        // Create scanner interface
        var scannerHtml = '<div class="scanqr-webform-scanner">' +
          '<div class="scanqr-scanner-container" id="scanner-' + elementId + '"></div>' +
          '<div class="scanqr-controls">' +
            '<button type="button" class="btn-start-scanner" data-target="' + $input.attr('id') + '">Start QR Scanner</button>' +
            '<button type="button" class="btn-stop-scanner hidden">Stop Scanner</button>' +
          '</div>' +
          '<div class="scanqr-status"></div>' +
        '</div>';
        
        $input.before($(scannerHtml));
        
        // Scanner functionality
        var $scannerContainer = $('#scanner-' + elementId);
        var $startBtn = $input.parent().find('.btn-start-scanner');
        var $stopBtn = $input.parent().find('.btn-stop-scanner');
        var $status = $input.parent().find('.scanqr-status');
        
        var video = null;
        var canvas = null;
        var context = null;
        var scanning = false;
        var stream = null;
        var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        function initScanner() {
          if (!video) {
            video = document.createElement('video');
            video.style.width = '100%';
            video.style.maxWidth = scannerWidth + 'px';
            video.style.height = scannerHeight + 'px';
            video.style.border = '2px solid #007cba';
            video.style.borderRadius = '8px';
            video.setAttribute('playsinline', true);
            
            canvas = document.createElement('canvas');
            context = canvas.getContext('2d');
            
            $scannerContainer.append(video);
          }
        }

        function startScanning() {
          initScanner();
          $status.html('<div class="scanqr-loading">Starting camera...</div>');
          
          if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            var constraints = {
              video: {
                facingMode: 'environment',
                width: { ideal: scannerWidth, min: 320, max: 1280 },
                height: { ideal: scannerHeight, min: 240, max: 720 }
              }
            };
            
            navigator.mediaDevices.getUserMedia(constraints)
              .then(function(mediaStream) {
                stream = mediaStream;
                video.srcObject = stream;
                video.play();
                
                scanning = true;
                $startBtn.addClass('hidden');
                $stopBtn.removeClass('hidden');
                $status.html('<div class="scanqr-active">Scanning for QR codes...</div>');
                
                requestAnimationFrame(tick);
              })
              .catch(function(err) {
                $status.html('<div class="scanqr-error">Camera error: ' + err.message + '</div>');
              });
          } else {
            $status.html('<div class="scanqr-error">Camera not supported in this browser.</div>');
          }
        }

        function stopScanning() {
          scanning = false;
          if (stream) {
            stream.getTracks().forEach(function(track) { track.stop(); });
            stream = null;
          }
          if (video) {
            video.srcObject = null;
          }
          $startBtn.removeClass('hidden');
          $stopBtn.addClass('hidden');
          $status.html('<div class="scanqr-stopped">Scanner stopped</div>');
        }

        function tick() {
          if (!scanning || !video) return;
          
          if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.height = video.videoHeight;
            canvas.width = video.videoWidth;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            
            if (typeof jsQR !== 'undefined') {
              var code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "dontInvert",
              });
              
              if (code) {
                handleQRDetection(code.data);
                return;
              }
            }
          }
          
          if (scanning) {
            requestAnimationFrame(tick);
          }
        }

        function handleQRDetection(qrContent) {
          $input.val(qrContent);
          $input.trigger('input').trigger('change');
          $status.html('<div class="scanqr-success">✓ QR Code detected!</div>');
          stopScanning();
        }

        // Event handlers
        $startBtn.on('click', startScanning);
        $stopBtn.on('click', stopScanning);
        
        $(window).on('beforeunload', function() {
          stopScanning();
        });
      });
    }
  };

})(jQuery, Drupal, once);