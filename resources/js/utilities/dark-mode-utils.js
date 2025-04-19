/**
 * Dark Mode Utility Functions
 * 
 * These utilities can be used outside of Vue components to handle dark mode styling
 */

/**
 * Get the current dark mode state
 * @returns {boolean} - True if dark mode is enabled
 */
export function isDarkMode() {
    return document.body.classList.contains('dark-mode');
}

/**
 * Apply appropriate text color class based on dark mode state
 * @param {HTMLElement} element - The DOM element to apply the class to
 * @param {string} lightClass - The class to apply in light mode
 * @param {string} darkClass - The class to apply in dark mode
 */
export function applyTextColorClass(element, lightClass, darkClass) {
    if (!element) return;
    
    // Remove both classes first
    element.classList.remove(lightClass, darkClass);
    
    // Add the appropriate class
    if (isDarkMode()) {
        element.classList.add(darkClass);
    } else {
        element.classList.add(lightClass);
    }
}

/**
 * Apply appropriate background color class based on dark mode state
 * @param {HTMLElement} element - The DOM element to apply the class to
 * @param {string} lightClass - The class to apply in light mode
 * @param {string} darkClass - The class to apply in dark mode
 */
export function applyBgColorClass(element, lightClass, darkClass) {
    if (!element) return;
    
    // Remove both classes first
    element.classList.remove(lightClass, darkClass);
    
    // Add the appropriate class
    if (isDarkMode()) {
        element.classList.add(darkClass);
    } else {
        element.classList.add(lightClass);
    }
}

/**
 * Apply appropriate border color class based on dark mode state
 * @param {HTMLElement} element - The DOM element to apply the class to
 * @param {string} lightClass - The class to apply in light mode
 * @param {string} darkClass - The class to apply in dark mode
 */
export function applyBorderColorClass(element, lightClass, darkClass) {
    if (!element) return;
    
    // Remove both classes first
    element.classList.remove(lightClass, darkClass);
    
    // Add the appropriate class
    if (isDarkMode()) {
        element.classList.add(darkClass);
    } else {
        element.classList.add(lightClass);
    }
}

/**
 * Apply CSS variable for text color based on type
 * @param {HTMLElement} element - The DOM element to apply the style to
 * @param {string} type - The type of text (primary, secondary, tertiary)
 */
export function applyTextColorStyle(element, type = 'primary') {
    if (!element) return;
    
    element.style.color = `var(--text-${type})`;
}

/**
 * Apply CSS variable for background color based on type
 * @param {HTMLElement} element - The DOM element to apply the style to
 * @param {string} type - The type of background (primary, secondary, tertiary)
 */
export function applyBgColorStyle(element, type = 'primary') {
    if (!element) return;
    
    element.style.backgroundColor = `var(--bg-${type})`;
}

/**
 * Setup a listener for dark mode changes
 * @param {Function} callback - Function to call when dark mode changes
 * @returns {Function} - Function to remove the listener
 */
export function onDarkModeChange(callback) {
    if (typeof callback !== 'function') return () => {};
    
    const handler = (event) => {
        callback(event.detail.darkMode);
    };
    
    document.addEventListener('darkmode-changed', handler);
    
    // Return a function to remove the listener
    return () => {
        document.removeEventListener('darkmode-changed', handler);
    };
}

/**
 * Apply dark mode classes to all elements matching a selector
 * @param {string} selector - CSS selector to find elements
 * @param {Object} options - Configuration object
 * @param {string} options.lightText - Light mode text color class
 * @param {string} options.darkText - Dark mode text color class
 * @param {string} options.lightBg - Light mode background color class
 * @param {string} options.darkBg - Dark mode background color class
 * @param {string} options.lightBorder - Light mode border color class
 * @param {string} options.darkBorder - Dark mode border color class
 */
export function applyDarkModeToSelector(selector, {
    lightText, darkText,
    lightBg, darkBg,
    lightBorder, darkBorder
} = {}) {
    const elements = document.querySelectorAll(selector);
    const isDark = isDarkMode();
    
    elements.forEach(element => {
        if (lightText && darkText) {
            element.classList.remove(lightText, darkText);
            element.classList.add(isDark ? darkText : lightText);
        }
        
        if (lightBg && darkBg) {
            element.classList.remove(lightBg, darkBg);
            element.classList.add(isDark ? darkBg : lightBg);
        }
        
        if (lightBorder && darkBorder) {
            element.classList.remove(lightBorder, darkBorder);
            element.classList.add(isDark ? darkBorder : lightBorder);
        }
    });
}

// Initialize dark mode listeners for non-Vue elements
document.addEventListener('DOMContentLoaded', () => {
    // Listen for dark mode changes
    document.addEventListener('darkmode-changed', (event) => {
        const isDark = event.detail.darkMode;
        
        // Apply to common text elements
        applyDarkModeToSelector('h1, h2, h3, h4, h5, h6', {
            lightText: 'text-gray-900',
            darkText: 'text-white'
        });
        
        applyDarkModeToSelector('p', {
            lightText: 'text-gray-700',
            darkText: 'text-gray-300'
        });
        
        applyDarkModeToSelector('label', {
            lightText: 'text-gray-700',
            darkText: 'text-gray-300'
        });
        
        // Apply to common UI elements
        applyDarkModeToSelector('.card, .panel', {
            lightBg: 'bg-white',
            darkBg: 'bg-gray-800',
            lightBorder: 'border-gray-200',
            darkBorder: 'border-gray-700'
        });
    });
});
