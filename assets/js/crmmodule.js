/**
 * CRMModule — JavaScript
 *
 * Minimal JS; leverages Bootstrap (already loaded by WHMCS) for modals,
 * alerts, and dismissals. No external dependencies required.
 *
 * @package    CRMModule
 * @author     HostingSpell LLP
 */

(function () {
    'use strict';

    // -------------------------------------------------------------------------
    // Confirm delete buttons
    // -------------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function () {
        var deleteLinks = document.querySelectorAll('.crm-confirm-delete');
        deleteLinks.forEach(function (el) {
            el.addEventListener('click', function (e) {
                var msg = el.getAttribute('data-confirm') || 'Are you sure you want to delete this item?';
                if (!window.confirm(msg)) {
                    e.preventDefault();
                }
            });
        });
    });

    // -------------------------------------------------------------------------
    // Profile image live preview on the edit_profile page
    // -------------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function () {
        var fileInput    = document.getElementById('crm-img-upload');
        var urlInput     = document.querySelector('input[name="profile_image_url"]');
        var previewImg   = document.getElementById('crm-img-preview');

        if (!previewImg) {
            return;
        }

        // File input: show local preview via FileReader
        if (fileInput) {
            fileInput.addEventListener('change', function () {
                var file = fileInput.files[0];
                if (!file) {
                    return;
                }

                // Basic client-side type check (server-side is the real validation)
                var allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (allowed.indexOf(file.type) === -1) {
                    alert('Please select a valid image file (JPG, PNG, GIF, WebP).');
                    fileInput.value = '';
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert('Image must be smaller than 2MB.');
                    fileInput.value = '';
                    return;
                }

                var reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                };
                reader.readAsDataURL(file);

                // Clear the URL field to avoid conflict
                if (urlInput) {
                    urlInput.value = '';
                }
            });
        }

        // URL input: update preview on blur
        if (urlInput) {
            urlInput.addEventListener('blur', function () {
                var url = urlInput.value.trim();
                if (url) {
                    var testImg = new Image();
                    testImg.onload  = function () { previewImg.src = url; };
                    testImg.onerror = function () {
                        alert('Could not load image from that URL. Please check the link.');
                    };
                    testImg.src = url;

                    // Clear file input to avoid conflict
                    if (fileInput) {
                        fileInput.value = '';
                    }
                }
            });
        }
    });

    // -------------------------------------------------------------------------
    // Auto-dismiss success alerts after 4 seconds
    // -------------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function () {
        var successAlerts = document.querySelectorAll('.alert-success.alert-dismissible');
        successAlerts.forEach(function (alert) {
            setTimeout(function () {
                // Use Bootstrap's alert dismiss if available
                if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.alert) {
                    jQuery(alert).alert('close');
                } else {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity .4s';
                    setTimeout(function () {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 400);
                }
            }, 4000);
        });
    });

})();
