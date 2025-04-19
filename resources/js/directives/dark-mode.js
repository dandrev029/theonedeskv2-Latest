/**
 * Dark Mode Directive
 * 
 * This directive automatically applies appropriate text colors based on dark mode state
 * Usage: v-dark-mode:text="'primary'" or v-dark-mode:bg="'secondary'" or v-dark-mode:border="'tertiary'"
 */

import Vue from 'vue';
import store from '@/store';

// Helper function to get the appropriate class based on type and mode
function getClass(type, mode, value) {
    const types = {
        text: {
            primary: {
                light: 'text-gray-900',
                dark: 'text-white'
            },
            secondary: {
                light: 'text-gray-700',
                dark: 'text-gray-300'
            },
            tertiary: {
                light: 'text-gray-500',
                dark: 'text-gray-400'
            },
            muted: {
                light: 'text-gray-400',
                dark: 'text-gray-500'
            }
        },
        bg: {
            primary: {
                light: 'bg-white',
                dark: 'bg-gray-800'
            },
            secondary: {
                light: 'bg-gray-50',
                dark: 'bg-gray-700'
            },
            tertiary: {
                light: 'bg-gray-100',
                dark: 'bg-gray-600'
            }
        },
        border: {
            primary: {
                light: 'border-gray-200',
                dark: 'border-gray-700'
            },
            secondary: {
                light: 'border-gray-300',
                dark: 'border-gray-600'
            }
        }
    };
    
    if (!types[type] || !types[type][value]) {
        console.warn(`Invalid v-dark-mode value: ${type}:${value}`);
        return '';
    }
    
    return types[type][value][mode];
}

// Create the directive
const darkModeDirective = {
    bind(el, binding, vnode) {
        // Get the type (text, bg, border) from the directive argument
        const type = binding.arg || 'text';
        // Get the value (primary, secondary, tertiary) from the binding value
        const value = binding.value || 'primary';
        
        // Apply initial class
        const isDarkMode = store.state.darkMode;
        const mode = isDarkMode ? 'dark' : 'light';
        const initialClass = getClass(type, mode, value);
        
        if (initialClass) {
            el.classList.add(initialClass);
        }
        
        // Store the opposite class for later removal
        el._darkModeData = {
            type,
            value,
            currentClass: initialClass,
            oppositeClass: getClass(type, isDarkMode ? 'light' : 'dark', value)
        };
        
        // Add event listener for dark mode changes
        const handleDarkModeChange = (event) => {
            if (!el._darkModeData) return;
            
            const { type, value, currentClass, oppositeClass } = el._darkModeData;
            const newMode = event.detail.darkMode ? 'dark' : 'light';
            const newClass = getClass(type, newMode, value);
            
            // Remove old class and add new class
            el.classList.remove(currentClass);
            el.classList.add(newClass);
            
            // Update stored data
            el._darkModeData.currentClass = newClass;
            el._darkModeData.oppositeClass = getClass(type, newMode === 'dark' ? 'light' : 'dark', value);
        };
        
        document.addEventListener('darkmode-changed', handleDarkModeChange);
        el._darkModeRemoveListener = () => {
            document.removeEventListener('darkmode-changed', handleDarkModeChange);
        };
    },
    
    update(el, binding) {
        // Handle value changes
        const type = binding.arg || 'text';
        const value = binding.value || 'primary';
        
        if (el._darkModeData && (el._darkModeData.type !== type || el._darkModeData.value !== value)) {
            // Remove old classes
            el.classList.remove(el._darkModeData.currentClass);
            
            // Apply new classes
            const isDarkMode = store.state.darkMode;
            const mode = isDarkMode ? 'dark' : 'light';
            const newClass = getClass(type, mode, value);
            
            if (newClass) {
                el.classList.add(newClass);
            }
            
            // Update stored data
            el._darkModeData.type = type;
            el._darkModeData.value = value;
            el._darkModeData.currentClass = newClass;
            el._darkModeData.oppositeClass = getClass(type, isDarkMode ? 'light' : 'dark', value);
        }
    },
    
    unbind(el) {
        // Clean up event listener
        if (el._darkModeRemoveListener) {
            el._darkModeRemoveListener();
        }
        
        // Remove data
        delete el._darkModeData;
    }
};

// Register the directive
Vue.directive('dark-mode', darkModeDirective);

export default darkModeDirective;
