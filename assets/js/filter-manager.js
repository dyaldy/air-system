/**
 * Filter utilities for air-system
 * 
 * This file provides reusable filter functionality for data tables
 * across different controllers (User, Pneumatic, Fitting, Storage).
 * 
 * @package AirSystem
 * @subpackage JavaScript
 * @author Apparel One Indonesia
 * @version 1.0.0
 */

/**
 * Generic Filter Manager
 * Handles filter dropdowns and form submissions consistently across controllers
 */
window.FilterManager = (function() {
    'use strict';

    /**
     * Initialize filter dropdowns with change event handlers
     * @param {Object} config - Configuration object with filter mappings
     * @param {string} config.controllerName - Name of the controller for form submission
     * @param {Object} config.filters - Filter configuration object
     * 
     * Example config:
     * {
     *   controllerName: 'user',
     *   filters: {
     *     'user-level': 'user_level',
     *     'department': 'department'
     *   }
     * }
     */
    function initFilters(config) {
        if (!config || !config.filters) {
            console.error('Filter configuration is required');
            return;
        }

        Object.keys(config.filters).forEach(function(filterId) {
            const filterElement = document.getElementById(filterId + '-filter');
            if (filterElement) {
                filterElement.addEventListener('change', function() {
                    applyFilter(filterId, config.filters[filterId], config.controllerName);
                });
            }
        });
    }

    /**
     * Apply a single filter
     * @param {string} filterId - HTML element ID of the filter
     * @param {string} filterName - Backend filter name
     * @param {string} controllerName - Controller name for form submission
     */
    function applyFilter(filterId, filterName, controllerName) {
        const filterElement = document.getElementById(filterId + '-filter');
        if (!filterElement) {
            console.error('Filter element not found:', filterId + '-filter');
            return;
        }

        const value = filterElement.value;
        
        // Build filter object
        const filterObj = {};
        if (value) {
            filterObj[filterName] = [value];
        }

        // Submit filter form
        submitFilterForm(filterObj, controllerName);
    }

    /**
     * Apply multiple filters at once
     * @param {Object} filterMap - Object mapping filter IDs to filter names
     * @param {string} controllerName - Controller name for form submission
     */
    function applyMultipleFilters(filterMap, controllerName) {
        const filterObj = {};

        Object.keys(filterMap).forEach(function(filterId) {
            const filterElement = document.getElementById(filterId + '-filter');
            if (filterElement && filterElement.value) {
                const filterName = filterMap[filterId];
                filterObj[filterName] = [filterElement.value];
            }
        });

        submitFilterForm(filterObj, controllerName);
    }

    /**
     * Submit filter form via POST
     * @param {Object} filterObj - Filter object to submit
     * @param {string} controllerName - Controller name (optional)
     */
    function submitFilterForm(filterObj, controllerName = '') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = controllerName ? '/' + controllerName : '';
        form.style.display = 'none';

        // Add filter data as JSON
        const filterInput = document.createElement('input');
        filterInput.type = 'hidden';
        filterInput.name = 'filter';
        filterInput.value = JSON.stringify(filterObj);
        form.appendChild(filterInput);

        document.body.appendChild(form);
        form.submit();
    }

    /**
     * Reset all filters
     * @param {string} controllerName - Controller name for form submission
     */
    function resetFilters(controllerName = '') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = controllerName ? '/' + controllerName : '';
        form.style.display = 'none';

        const resetInput = document.createElement('input');
        resetInput.type = 'hidden';
        resetInput.name = 'reset';
        resetInput.value = '1';
        form.appendChild(resetInput);

        document.body.appendChild(form);
        form.submit();
    }

    /**
     * Initialize filter reset button
     * @param {string} buttonId - ID of the reset button
     * @param {string} controllerName - Controller name
     */
    function initResetButton(buttonId, controllerName) {
        const resetButton = document.getElementById(buttonId);
        if (resetButton) {
            resetButton.addEventListener('click', function(e) {
                e.preventDefault();
                resetFilters(controllerName);
            });
        }
    }

    /**
     * Set filter values from server data (for maintaining state after page reload)
     * @param {Object} filterData - Filter data from server
     */
    function setFilterValues(filterData) {
        if (!filterData) return;

        Object.keys(filterData).forEach(function(filterName) {
            const filterValue = Array.isArray(filterData[filterName]) 
                ? filterData[filterName][0] 
                : filterData[filterName];

            // Try to find corresponding filter element
            // Look for elements with IDs ending in '-filter'
            const possibleIds = [
                filterName + '-filter',
                filterName.replace('_', '-') + '-filter',
                filterName.replace('-', '_') + '-filter'
            ];

            possibleIds.forEach(function(id) {
                const element = document.getElementById(id);
                if (element) {
                    element.value = filterValue;
                }
            });
        });
    }

    /**
     * Initialize dependent dropdowns (e.g., type -> subtype)
     * @param {Object} config - Configuration for dependent dropdowns
     * 
     * Example config:
     * {
     *   parentId: 'type-filter',
     *   childId: 'subtype-filter',
     *   ajaxUrl: '/fitting/get_subtypes',
     *   emptyOption: 'Pilih Subtype'
     * }
     */
    function initDependentDropdowns(config) {
        const parentElement = document.getElementById(config.parentId);
        const childElement = document.getElementById(config.childId);

        if (!parentElement || !childElement) {
            return;
        }

        parentElement.addEventListener('change', function() {
            const parentValue = this.value;
            
            // Clear child dropdown
            childElement.innerHTML = '<option value="">' + (config.emptyOption || 'Pilih...') + '</option>';

            if (parentValue) {
                // Fetch child options via AJAX
                fetch(config.ajaxUrl + '?parent=' + encodeURIComponent(parentValue))
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' && data.options) {
                            data.options.forEach(option => {
                                const optionElement = document.createElement('option');
                                optionElement.value = option.value || option;
                                optionElement.textContent = option.text || option;
                                childElement.appendChild(optionElement);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching dependent options:', error);
                    });
            }
        });
    }

    // Public API
    return {
        initFilters: initFilters,
        applyFilter: applyFilter,
        applyMultipleFilters: applyMultipleFilters,
        resetFilters: resetFilters,
        initResetButton: initResetButton,
        setFilterValues: setFilterValues,
        initDependentDropdowns: initDependentDropdowns
    };
})();

/**
 * Auto-initialize common filter functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize reset button if present
    const resetButton = document.getElementById('reset-filters');
    if (resetButton) {
        const controllerName = resetButton.getAttribute('data-controller') || '';
        FilterManager.initResetButton('reset-filters', controllerName);
    }
});