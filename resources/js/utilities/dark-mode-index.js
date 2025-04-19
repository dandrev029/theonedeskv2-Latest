/**
 * Dark Mode Utilities Index
 * 
 * This file exports all dark mode utilities for easy importing
 */

// Export all utilities from dark-mode-utils.js
export * from './dark-mode-utils';

// Export all utilities from tinymce-dark-mode.js
export * from './tinymce-dark-mode';

// Export all utilities from chart-dark-mode.js
export * from './chart-dark-mode';

// Export all utilities from datatable-dark-mode.js
export * from './datatable-dark-mode';

// Export default object with all utilities
export default {
    // Dark Mode Utils
    isDarkMode: require('./dark-mode-utils').isDarkMode,
    applyTextColorClass: require('./dark-mode-utils').applyTextColorClass,
    applyBgColorClass: require('./dark-mode-utils').applyBgColorClass,
    applyBorderColorClass: require('./dark-mode-utils').applyBorderColorClass,
    applyTextColorStyle: require('./dark-mode-utils').applyTextColorStyle,
    applyBgColorStyle: require('./dark-mode-utils').applyBgColorStyle,
    onDarkModeChange: require('./dark-mode-utils').onDarkModeChange,
    applyDarkModeToSelector: require('./dark-mode-utils').applyDarkModeToSelector,
    
    // TinyMCE Dark Mode Utils
    applyDarkModeToEditor: require('./tinymce-dark-mode').applyDarkModeToEditor,
    setupEditorDarkModeListener: require('./tinymce-dark-mode').setupEditorDarkModeListener,
    getTinyMCEOptions: require('./tinymce-dark-mode').getTinyMCEOptions,
    
    // Chart.js Dark Mode Utils
    getChartOptions: require('./chart-dark-mode').getChartOptions,
    applyDarkModeToChart: require('./chart-dark-mode').applyDarkModeToChart,
    setupChartDarkModeListener: require('./chart-dark-mode').setupChartDarkModeListener,
    
    // DataTables Dark Mode Utils
    getDataTableOptions: require('./datatable-dark-mode').getDataTableOptions,
    applyDarkModeToDataTable: require('./datatable-dark-mode').applyDarkModeToDataTable
};
