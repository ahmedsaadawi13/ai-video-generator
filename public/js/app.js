// FILE: /public/js/app.js
/**
 * AI Video Generator - Main JavaScript File
 */

(function() {
    'use strict';

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });

    // Confirm delete actions
    const deleteForms = document.querySelectorAll('form[action*="/delete"]');
    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Platform preset auto-select aspect ratio
    const platformPreset = document.getElementById('platform_preset');
    const aspectRatio = document.getElementById('aspect_ratio');

    if (platformPreset && aspectRatio) {
        platformPreset.addEventListener('change', function() {
            const preset = this.value;

            switch(preset) {
                case 'youtube_horizontal':
                    aspectRatio.value = '16:9';
                    break;
                case 'tiktok_vertical':
                case 'instagram_story':
                    aspectRatio.value = '9:16';
                    break;
                case 'square_social':
                    aspectRatio.value = '1:1';
                    break;
            }
        });
    }

    // File upload preview
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                console.log('File selected:', file.name, '(' + fileSize + ' MB)');
            }
        });
    }

    // Auto-refresh render job status (if on render details page)
    const renderJobStatus = document.querySelector('.render-job-status');
    if (renderJobStatus) {
        const status = renderJobStatus.dataset.status;

        if (status === 'queued' || status === 'processing') {
            // Refresh page every 5 seconds to check status
            setTimeout(function() {
                window.location.reload();
            }, 5000);
        }
    }

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#e74c3c';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });

    // Copy API key to clipboard
    const apiKeyCode = document.querySelector('.alert code');
    if (apiKeyCode) {
        apiKeyCode.style.cursor = 'pointer';
        apiKeyCode.title = 'Click to copy';

        apiKeyCode.addEventListener('click', function() {
            const text = this.textContent;
            navigator.clipboard.writeText(text).then(function() {
                alert('API key copied to clipboard!');
            });
        });
    }

    // Usage progress bar animation
    const usageProgress = document.querySelectorAll('.usage-progress');
    usageProgress.forEach(function(bar) {
        const width = bar.style.width;
        bar.style.width = '0';

        setTimeout(function() {
            bar.style.width = width;
        }, 100);
    });

    console.log('AI Video Generator - Application loaded');
})();
