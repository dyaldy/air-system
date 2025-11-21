/**
 * Common JavaScript utilities for air-system
 * 
 * This file contains reusable JavaScript functions used across multiple pages
 * to maintain consistency and reduce code duplication.
 * 
 * @package AirSystem
 * @subpackage JavaScript
 * @author Apparel One Indonesia
 * @version 1.0.0
 */

/**
 * Air System Common Utilities
 */
window.AirSystemUtils = (function() {
    'use strict';

    /**
     * Display a success message using Bootstrap alert
     * @param {string} message - The success message to display
     * @param {string} containerId - ID of container to append alert (default: 'alert-container')
     */
    function showSuccessMessage(message, containerId = 'alert-container') {
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn('Alert container not found:', containerId);
            return;
        }

        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        container.innerHTML = alertHtml;
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }

    /**
     * Display an error message using Bootstrap alert
     * @param {string} message - The error message to display
     * @param {string} containerId - ID of container to append alert (default: 'alert-container')
     */
    function showErrorMessage(message, containerId = 'alert-container') {
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn('Alert container not found:', containerId);
            return;
        }

        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        container.innerHTML = alertHtml;
    }

    /**
     * Validate file input for CSV files
     * @param {HTMLInputElement} fileInput - The file input element
     * @param {number} maxSizeKB - Maximum file size in KB (default: 2048)
     * @returns {Object} Validation result with isValid boolean and message string
     */
    function validateCSVFile(fileInput, maxSizeKB = 2048) {
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            return { isValid: false, message: 'Harap pilih file sebelum mengupload!' };
        }

        const file = fileInput.files[0];
        const maxSizeBytes = maxSizeKB * 1024;

        // Check file size
        if (file.size > maxSizeBytes) {
            return { 
                isValid: false, 
                message: `Ukuran file terlalu besar. Maksimal ${maxSizeKB}KB.` 
            };
        }

        // Check file type
        const allowedTypes = [
            'text/csv', // .csv
            'application/csv',
            'text/comma-separated-values',
            'application/vnd.ms-excel' // Some systems use this for CSV
        ];

        if (!allowedTypes.includes(file.type) && !file.name.endsWith('.csv')) {
            return { 
                isValid: false, 
                message: 'Format file tidak valid! Hanya file .csv yang diperbolehkan.' 
            };
        }

        return { isValid: true, message: 'File valid' };
    }

    /**
     * Validate file input for Excel files (kept for backward compatibility)
     * @param {HTMLInputElement} fileInput - The file input element
     * @param {number} maxSizeKB - Maximum file size in KB (default: 2048)
     * @returns {Object} Validation result with isValid boolean and message string
     * @deprecated Use validateCSVFile instead
     */
    function validateExcelFile(fileInput, maxSizeKB = 2048) {
        return validateCSVFile(fileInput, maxSizeKB);
    }

    /**
     * Handle CSV file upload with validation
     * @param {string} formId - ID of the upload form
     * @param {string} fileInputId - ID of the file input element
     * @param {Function} onSuccess - Callback function on successful validation
     * @param {Function} onError - Callback function on validation error
     */
    function handleCSVUpload(formId, fileInputId, onSuccess, onError) {
        const form = document.getElementById(formId);
        const fileInput = document.getElementById(fileInputId);
        
        if (!form || !fileInput) {
            console.error('Form or file input not found');
            return;
        }
        
        const validation = validateCSVFile(fileInput);
        
        if (validation.isValid) {
            if (typeof onSuccess === 'function') {
                onSuccess(form, fileInput);
            } else {
                form.submit();
            }
        } else {
            if (typeof onError === 'function') {
                onError(validation.message);
            } else {
                alert(validation.message);
            }
        }
    }

    /**
     * Handle Excel file upload with validation (kept for backward compatibility)
     * @deprecated Use handleCSVUpload instead
     */
    function handleExcelUpload(formId, fileInputId, onSuccess, onError) {
        return handleCSVUpload(formId, fileInputId, onSuccess, onError);
    }

    // Legacy function handler - keeping for old code
    const _legacyHandleExcelUpload = function(formId, fileInputId, onSuccess, onError) {
        const form = document.getElementById(formId);
        const fileInput = document.getElementById(fileInputId);

        if (!form || !fileInput) {
            console.error('Form or file input element not found');
            return;
        }

        const validation = validateExcelFile(fileInput);
        
        if (validation.isValid) {
            if (typeof onSuccess === 'function') {
                onSuccess(form, fileInput);
            } else {
                form.submit();
            }
        } else {
            if (typeof onError === 'function') {
                onError(validation.message);
            } else {
                alert(validation.message);
            }
        }
    }

    /**
     * Apply filters by building and submitting a POST form
     * @param {Object} filterData - Filter data as key-value pairs
     * @param {string} action - Form action URL (optional, defaults to current page)
     */
    function applyFilters(filterData, action = '') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        form.style.display = 'none';

        // Add filter data as hidden inputs
        Object.keys(filterData).forEach(key => {
            if (filterData[key]) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'filter-' + key;
                input.value = filterData[key];
                form.appendChild(input);

                // Also add the submit button name
                const submitInput = document.createElement('input');
                submitInput.type = 'hidden';
                submitInput.name = key;
                submitInput.value = '1';
                form.appendChild(submitInput);
            }
        });

        document.body.appendChild(form);
        form.submit();
    }

    /**
     * Format number with thousand separators (Indonesian format)
     * @param {number} num - Number to format
     * @returns {string} Formatted number string
     */
    function formatNumber(num) {
        if (isNaN(num)) return '0';
        return parseInt(num).toLocaleString('id-ID');
    }

    /**
     * Confirm deletion action
     * @param {string} itemName - Name of item to be deleted
     * @param {Function} onConfirm - Callback function when confirmed
     * @returns {boolean} True if confirmed, false otherwise
     */
    function confirmDelete(itemName, onConfirm) {
        const message = `Apakah Anda yakin ingin menghapus ${itemName}? Tindakan ini tidak dapat dibatalkan.`;
        
        if (confirm(message)) {
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
            return true;
        }
        return false;
    }

    /**
     * Initialize common page functionality
     * This function is called automatically when the DOM is ready
     */
    function init() {
        // Initialize tooltips if Bootstrap is available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Initialize popovers if Bootstrap is available
        if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        }

        // Add confirmation to delete buttons
        const deleteButtons = document.querySelectorAll('.btn-delete, [data-action="delete"]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const itemName = this.getAttribute('data-item-name') || 'item ini';
                if (!confirmDelete(itemName)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Public API
    return {
        showSuccessMessage: showSuccessMessage,
        showErrorMessage: showErrorMessage,
        validateExcelFile: validateExcelFile,
        handleExcelUpload: handleExcelUpload,
        applyFilters: applyFilters,
        formatNumber: formatNumber,
        confirmDelete: confirmDelete,
        init: init
    };
})();

/**
 * Legacy support - Global functions for backward compatibility
 */
window.showSuccessMessage = window.AirSystemUtils.showSuccessMessage;
window.showErrorMessage = window.AirSystemUtils.showErrorMessage;
window.validateExcelFile = window.AirSystemUtils.validateExcelFile;
window.handleExcelUpload = window.AirSystemUtils.handleExcelUpload;
window.applyFilters = window.AirSystemUtils.applyFilters;
window.formatNumber = window.AirSystemUtils.formatNumber;
window.confirmDelete = window.AirSystemUtils.confirmDelete;