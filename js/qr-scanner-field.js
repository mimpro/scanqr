(function ($, Drupal, once) {
  'use strict';

  /**
   * QR Scanner Field behavior.
   */
  Drupal.behaviors.scanQRField = {
    attach: function (context, settings) {
      $('.scanqr-field-widget', context).each(function() {
        var $widget = $(this);
        
        // Check if already processed
        if ($widget.hasClass('scanqr-processed')) {
          return;
        }
        $widget.addClass('scanqr-processed');
        var $widget = $(this);
        var $container = $widget.find('.scanqr-scanner-container');
        var $startBtn = $widget.find('.btn-start-scanner');
        var $stopBtn = $widget.find('.btn-stop-scanner');
        var $status = $widget.find('.scanqr-status');
        var $contentField = $widget.find('.scanqr-content-field');
        
        var fieldName = $container.data('field-name');
        var fieldDelta = $container.data('field-delta');
        var scannerWidth = $container.data('scanner-width') || 400;
        var scannerHeight = $container.data('scanner-height') || 300;
        
        var video = null;
        var canvas = null;
        var context = null;
        var scanning = false;
        var stream = null;
        var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        // Mobile-specific adjustments
        if (isMobile) {
          scannerWidth = Math.min(scannerWidth, window.innerWidth - 40);
          scannerHeight = Math.min(scannerHeight, 300);
        }

        // Initialize scanner elements
        function initScanner() {
          if (!video) {
            video = document.createElement('video');
            video.style.width = scannerWidth + 'px';
            video.style.height = scannerHeight + 'px';
            video.style.border = '2px solid #007cba';
            video.style.borderRadius = '8px';
            video.setAttribute('playsinline', true);
            
            canvas = document.createElement('canvas');
            context = canvas.getContext('2d');
            
            $container.append(video);
          }
        }

        // Start camera and scanning
        function startScanning() {
          initScanner();
          
          $status.html('<div class="scanqr-loading">Starting camera...</div>');
          
          if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            // Mobile-optimized camera constraints
            var constraints = {
              video: {
                facingMode: 'environment', // Prefer back camera for QR scanning
                width: { 
                  ideal: scannerWidth,
                  min: 320,
                  max: 1280
                },
                height: { 
                  ideal: scannerHeight,
                  min: 240,
                  max: 720
                },
                // Mobile optimizations
                frameRate: { ideal: 30, max: 60 },
                focusMode: 'continuous',
                advanced: [
                  { focusMode: 'continuous' },
                  { exposureMode: 'continuous' }
                ]
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
              $status.html('<div class="scanqr-active">Scanning for QR codes... Point camera at QR code</div>');
              
              // Start the scanning loop
              requestAnimationFrame(tick);
            })
            .catch(function(err) {
              $status.html('<div class="scanqr-error">Camera access denied or not available: ' + err.message + '</div>');
              console.error('Camera error:', err);
            });
          } else {
            $status.html('<div class="scanqr-error">Camera not supported in this browser. Please use a modern browser.</div>');
          }
        }

        // Stop scanning
        function stopScanning() {
          scanning = false;
          
          if (stream) {
            stream.getTracks().forEach(function(track) {
              track.stop();
            });
            stream = null;
          }
          
          if (video) {
            video.srcObject = null;
          }
          
          $startBtn.removeClass('hidden');
          $stopBtn.addClass('hidden');
          $status.html('<div class="scanqr-stopped">Scanner stopped</div>');
        }

        // Scanning loop
        function tick() {
          if (!scanning || !video) return;
          
          if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.height = video.videoHeight;
            canvas.width = video.videoWidth;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            
            // Use jsQR to decode QR code with mobile optimizations
            if (typeof jsQR !== 'undefined') {
              var code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "dontInvert",
                // Mobile optimization: scan smaller region for better performance
                locateOptions: {
                  tryHarder: isMobile,
                  maxSize: isMobile ? 500 : 1000
                }
              });
              
              if (code) {
                handleQRDetection(code.data);
                return; // Stop scanning after successful detection
              }
            }
          }
          
          if (scanning) {
            requestAnimationFrame(tick);
          }
        }

        // Handle successful QR detection
        function handleQRDetection(qrContent) {
          // Fill the content field
          $contentField.val(qrContent);
          
          // Set timestamp
          var timestampField = $widget.find('input[name*="[scanned_at]"]');
          if (timestampField.length) {
            timestampField.val(Math.floor(Date.now() / 1000));
          }
          
          // Update status
          $status.html('<div class="scanqr-success">✓ QR Code detected successfully!</div>');
          
          // Stop scanning
          stopScanning();
          
          // Trigger change event for Drupal form handling
          $contentField.trigger('change');
          
          // Optional: Play success sound
          playSuccessSound();
          
          console.log('QR Code detected:', qrContent);
        }

        // Play success sound (optional)
        function playSuccessSound() {
          try {
            var audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSEFKX/O8+SMOB0PVqzn77FdGAg+ltryxnkiAy2BzvLongkdmtj0y3srFSp+4/CwcBcFHm7EmtXxSkQKFmqQzOijUBlG');
            audio.play().catch(function() {
              // Ignore audio play failures
            });
          } catch (e) {
            // Ignore audio errors
          }
        }

        // Event handlers
        $startBtn.on('click', function(e) {
          e.preventDefault();
          startScanning();
        });

        $stopBtn.on('click', function(e) {
          e.preventDefault();
          stopScanning();
        });

        // Cleanup on page unload
        $(window).on('beforeunload', function() {
          stopScanning();
        });
      });
    }
  };

})(jQuery, Drupal, once);