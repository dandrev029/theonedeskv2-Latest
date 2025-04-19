/**
 * Chart.js Dark Mode Utilities
 * 
 * Helper functions to apply dark mode to Chart.js charts
 */

import store from '@/store';

/**
 * Get Chart.js options with dark mode support
 * @param {Object} options - Custom Chart.js options
 * @returns {Object} - Chart.js options with dark mode support
 */
export function getChartOptions(options = {}) {
    const isDarkMode = store.state.darkMode;
    
    // Default options with dark mode support
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
            labels: {
                fontColor: isDarkMode ? '#e5e7eb' : '#4b5563'
            }
        },
        scales: {
            xAxes: [{
                ticks: {
                    fontColor: isDarkMode ? '#d1d5db' : '#6b7280'
                },
                gridLines: {
                    color: isDarkMode ? 'rgba(75, 85, 99, 0.2)' : 'rgba(229, 231, 235, 0.5)',
                    zeroLineColor: isDarkMode ? 'rgba(75, 85, 99, 0.5)' : 'rgba(209, 213, 219, 0.5)'
                }
            }],
            yAxes: [{
                ticks: {
                    fontColor: isDarkMode ? '#d1d5db' : '#6b7280'
                },
                gridLines: {
                    color: isDarkMode ? 'rgba(75, 85, 99, 0.2)' : 'rgba(229, 231, 235, 0.5)',
                    zeroLineColor: isDarkMode ? 'rgba(75, 85, 99, 0.5)' : 'rgba(209, 213, 219, 0.5)'
                }
            }]
        },
        tooltips: {
            backgroundColor: isDarkMode ? '#1f2937' : '#ffffff',
            titleFontColor: isDarkMode ? '#f9fafb' : '#111827',
            bodyFontColor: isDarkMode ? '#e5e7eb' : '#4b5563',
            borderColor: isDarkMode ? '#374151' : '#e5e7eb',
            borderWidth: 1
        }
    };
    
    // Deep merge default options with custom options
    return deepMerge(defaultOptions, options);
}

/**
 * Apply dark mode to an existing Chart.js instance
 * @param {Object} chart - Chart.js instance
 */
export function applyDarkModeToChart(chart) {
    if (!chart) return;
    
    const isDarkMode = store.state.darkMode;
    
    // Update legend colors
    if (chart.options.legend && chart.options.legend.labels) {
        chart.options.legend.labels.fontColor = isDarkMode ? '#e5e7eb' : '#4b5563';
    }
    
    // Update scales colors
    if (chart.options.scales) {
        // X Axes
        if (chart.options.scales.xAxes && chart.options.scales.xAxes.length > 0) {
            chart.options.scales.xAxes.forEach(axis => {
                if (axis.ticks) {
                    axis.ticks.fontColor = isDarkMode ? '#d1d5db' : '#6b7280';
                }
                if (axis.gridLines) {
                    axis.gridLines.color = isDarkMode ? 'rgba(75, 85, 99, 0.2)' : 'rgba(229, 231, 235, 0.5)';
                    axis.gridLines.zeroLineColor = isDarkMode ? 'rgba(75, 85, 99, 0.5)' : 'rgba(209, 213, 219, 0.5)';
                }
            });
        }
        
        // Y Axes
        if (chart.options.scales.yAxes && chart.options.scales.yAxes.length > 0) {
            chart.options.scales.yAxes.forEach(axis => {
                if (axis.ticks) {
                    axis.ticks.fontColor = isDarkMode ? '#d1d5db' : '#6b7280';
                }
                if (axis.gridLines) {
                    axis.gridLines.color = isDarkMode ? 'rgba(75, 85, 99, 0.2)' : 'rgba(229, 231, 235, 0.5)';
                    axis.gridLines.zeroLineColor = isDarkMode ? 'rgba(75, 85, 99, 0.5)' : 'rgba(209, 213, 219, 0.5)';
                }
            });
        }
    }
    
    // Update tooltip colors
    if (chart.options.tooltips) {
        chart.options.tooltips.backgroundColor = isDarkMode ? '#1f2937' : '#ffffff';
        chart.options.tooltips.titleFontColor = isDarkMode ? '#f9fafb' : '#111827';
        chart.options.tooltips.bodyFontColor = isDarkMode ? '#e5e7eb' : '#4b5563';
        chart.options.tooltips.borderColor = isDarkMode ? '#374151' : '#e5e7eb';
    }
    
    // Update the chart
    chart.update();
}

/**
 * Setup dark mode listener for Chart.js instance
 * @param {Object} chart - Chart.js instance
 * @returns {Function} - Function to remove the listener
 */
export function setupChartDarkModeListener(chart) {
    if (!chart) return () => {};
    
    const handleDarkModeChange = (event) => {
        applyDarkModeToChart(chart);
    };
    
    document.addEventListener('darkmode-changed', handleDarkModeChange);
    
    // Return a function to remove the listener
    return () => {
        document.removeEventListener('darkmode-changed', handleDarkModeChange);
    };
}

/**
 * Deep merge two objects
 * @param {Object} target - Target object
 * @param {Object} source - Source object
 * @returns {Object} - Merged object
 */
function deepMerge(target, source) {
    const output = { ...target };
    
    if (isObject(target) && isObject(source)) {
        Object.keys(source).forEach(key => {
            if (isObject(source[key])) {
                if (!(key in target)) {
                    output[key] = source[key];
                } else {
                    output[key] = deepMerge(target[key], source[key]);
                }
            } else {
                output[key] = source[key];
            }
        });
    }
    
    return output;
}

/**
 * Check if value is an object
 * @param {*} item - Value to check
 * @returns {boolean} - True if value is an object
 */
function isObject(item) {
    return (item && typeof item === 'object' && !Array.isArray(item));
}
