(function ($, Drupal, once) {
  'use strict';

  /**
   * QR Scanner behavior.
   */
  Drupal.behaviors.scanQR = {
    attach: function (context, settings) {
      $('#qr-scanner', context).each(function () {
        var $element = $(this);
        
        // Check if already processed
        if ($element.hasClass('scanqr-processed')) {
          return;
        }
        $element.addClass('scanqr-processed');
        var video = document.createElement('video');
        video.id = 'qr-scanner-video';
        video.style.width = '100%';
        video.style.maxWidth = '400px';
        video.style.height = '300px';
        
        $(this).append('<div class="scanqr-loading">Initializing camera...</div>');
        $(this).append(video);
        
        // Initialize camera
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
          navigator.mediaDevices.getUserMedia({ 
            video: { 
              facingMode: 'environment' // Use back camera if available
            } 
          })
          .then(function(stream) {
            $('.scanqr-loading').remove();
            video.srcObject = stream;
            video.setAttribute('playsinline', true);
            video.play();
            
            // Start QR scanning (simplified version)
            startQRScanning(video);
          })
          .catch(function(err) {
            $('.scanqr-loading').html('<div class="scanqr-error">Camera access denied or not available: ' + err.message + '</div>');
          });
        } else {
          $('.scanqr-loading').html('<div class="scanqr-error">Camera not supported in this browser.</div>');
        }
      });
    }
  };
  
  /**
   * Simplified QR scanning function.
   * Note: This is a basic implementation. For production use, integrate with a proper QR library like jsQR.
   */
  function startQRScanning(video) {
    // This is a placeholder for QR scanning logic
    // In a real implementation, you would use a library like jsQR or QuaggaJS
    
    var canvas = document.createElement('canvas');
    var context = canvas.getContext('2d');
    
    function tick() {
      if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.height = video.videoHeight;
        canvas.width = video.videoWidth;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Here you would integrate with a QR code detection library
        // For now, we'll simulate a successful scan after 5 seconds
        setTimeout(function() {
          if (!$('#qr-result').is(':visible')) {
            simulateQRScan('https://example.com/sample-qr-code');
          }
        }, 5000);
      }
      
      // Continue scanning
      requestAnimationFrame(tick);
    }
    
    requestAnimationFrame(tick);
  }
  
  /**
   * Simulate a QR code scan result.
   */
  function simulateQRScan(result) {
    $('#qr-result-text').text(result);
    $('#qr-result').show();
    
    // Optional: Play sound if enabled
    // You could add sound functionality here
    
    console.log('QR Code detected:', result);
  }
  
})(jQuery, Drupal, once);