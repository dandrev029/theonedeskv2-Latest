/**
 * DataTables Dark Mode Utilities
 * 
 * Helper functions to apply dark mode to DataTables
 */

import store from '@/store';
import { onDarkModeChange } from './dark-mode-utils';

/**
 * Get DataTables options with dark mode support
 * @param {Object} options - Custom DataTables options
 * @returns {Object} - DataTables options with dark mode support
 */
export function getDataTableOptions(options = {}) {
    const isDarkMode = store.state.darkMode;
    
    // Default options with dark mode support
    const defaultOptions = {
        // Add dark mode class to the table
        drawCallback: function(settings) {
            const api = this.api();
            const table = api.table().node();
            
            if (isDarkMode) {
                table.classList.add('dark-mode-table');
            } else {
                table.classList.remove('dark-mode-table');
            }
            
            // Call custom drawCallback if provided
            if (options.drawCallback) {
                options.drawCallback.call(this, settings);
            }
        },
        
        // Add dark mode class to the wrapper
        initComplete: function(settings, json) {
            const api = this.api();
            const wrapper = api.table().container();
            
            if (isDarkMode) {
                wrapper.classList.add('dark-mode-datatable');
            } else {
                wrapper.classList.remove('dark-mode-datatable');
            }
            
            // Setup dark mode listener
            const removeListener = onDarkModeChange((isDark) => {
                if (isDark) {
                    wrapper.classList.add('dark-mode-datatable');
                    api.table().node().classList.add('dark-mode-table');
                } else {
                    wrapper.classList.remove('dark-mode-datatable');
                    api.table().node().classList.remove('dark-mode-table');
                }
                
                // Adjust search input
                const searchInput = wrapper.querySelector('.dataTables_filter input');
                if (searchInput) {
                    if (isDark) {
                        searchInput.classList.add('dark-mode-input');
                    } else {
                        searchInput.classList.remove('dark-mode-input');
                    }
                }
                
                // Adjust length select
                const lengthSelect = wrapper.querySelector('.dataTables_length select');
                if (lengthSelect) {
                    if (isDark) {
                        lengthSelect.classList.add('dark-mode-select');
                    } else {
                        lengthSelect.classList.remove('dark-mode-select');
                    }
                }
                
                // Adjust pagination
                const pagination = wrapper.querySelector('.dataTables_paginate');
                if (pagination) {
                    if (isDark) {
                        pagination.classList.add('dark-mode-pagination');
                    } else {
                        pagination.classList.remove('dark-mode-pagination');
                    }
                }
            });
            
            // Store the listener removal function
            wrapper._darkModeRemoveListener = removeListener;
            
            // Call custom initComplete if provided
            if (options.initComplete) {
                options.initComplete.call(this, settings, json);
            }
        },
        
        // Clean up when the table is destroyed
        destroy: function(settings) {
            const api = this.api();
            const wrapper = api.table().container();
            
            // Remove dark mode listener
            if (wrapper._darkModeRemoveListener) {
                wrapper._darkModeRemoveListener();
                delete wrapper._darkModeRemoveListener;
            }
            
            // Call custom destroy if provided
            if (options.destroy) {
                options.destroy.call(this, settings);
            }
        }
    };
    
    // Merge default options with custom options
    return { ...defaultOptions, ...options };
}

/**
 * Apply dark mode to an existing DataTable instance
 * @param {Object} dataTable - DataTable instance
 */
export function applyDarkModeToDataTable(dataTable) {
    if (!dataTable) return;
    
    const isDarkMode = store.state.darkMode;
    const table = dataTable.table().node();
    const wrapper = dataTable.table().container();
    
    // Apply dark mode to the table
    if (isDarkMode) {
        table.classList.add('dark-mode-table');
    } else {
        table.classList.remove('dark-mode-table');
    }
    
    // Apply dark mode to the wrapper
    if (isDarkMode) {
        wrapper.classList.add('dark-mode-datatable');
    } else {
        wrapper.classList.remove('dark-mode-datatable');
    }
    
    // Adjust search input
    const searchInput = wrapper.querySelector('.dataTables_filter input');
    if (searchInput) {
        if (isDarkMode) {
            searchInput.classList.add('dark-mode-input');
        } else {
            searchInput.classList.remove('dark-mode-input');
        }
    }
    
    // Adjust length select
    const lengthSelect = wrapper.querySelector('.dataTables_length select');
    if (lengthSelect) {
        if (isDarkMode) {
            lengthSelect.classList.add('dark-mode-select');
        } else {
            lengthSelect.classList.remove('dark-mode-select');
        }
    }
    
    // Adjust pagination
    const pagination = wrapper.querySelector('.dataTables_paginate');
    if (pagination) {
        if (isDarkMode) {
            pagination.classList.add('dark-mode-pagination');
        } else {
            pagination.classList.remove('dark-mode-pagination');
        }
    }
}
