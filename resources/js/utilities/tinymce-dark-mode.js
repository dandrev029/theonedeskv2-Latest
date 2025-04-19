/**
 * TinyMCE Dark Mode Utilities
 * 
 * Helper functions to apply dark mode to TinyMCE editor
 */

import store from '@/store';

/**
 * Apply dark mode to TinyMCE editor
 * @param {Object} editor - TinyMCE editor instance
 */
export function applyDarkModeToEditor(editor) {
    if (!editor) return;
    
    const isDarkMode = store.state.darkMode;
    
    // Set the skin based on dark mode
    if (isDarkMode) {
        editor.options.set('skin', 'oxide-dark');
        editor.options.set('content_css', 'dark');
    } else {
        editor.options.set('skin', 'oxide');
        editor.options.set('content_css', 'default');
    }
    
    // Apply dark mode to the editor container
    const editorContainer = editor.getContainer();
    if (editorContainer) {
        if (isDarkMode) {
            editorContainer.classList.add('tox-tinymce--dark');
        } else {
            editorContainer.classList.remove('tox-tinymce--dark');
        }
    }
    
    // Apply dark mode to the editor content
    const editorBody = editor.getBody();
    if (editorBody) {
        if (isDarkMode) {
            editorBody.classList.add('dark-mode');
            editorBody.style.backgroundColor = '#1f2937';
            editorBody.style.color = '#f9fafb';
        } else {
            editorBody.classList.remove('dark-mode');
            editorBody.style.backgroundColor = '';
            editorBody.style.color = '';
        }
    }
}

/**
 * Setup dark mode listener for TinyMCE editor
 * @param {Object} editor - TinyMCE editor instance
 * @returns {Function} - Function to remove the listener
 */
export function setupEditorDarkModeListener(editor) {
    if (!editor) return () => {};
    
    const handleDarkModeChange = (event) => {
        applyDarkModeToEditor(editor);
    };
    
    document.addEventListener('darkmode-changed', handleDarkModeChange);
    
    // Return a function to remove the listener
    return () => {
        document.removeEventListener('darkmode-changed', handleDarkModeChange);
    };
}

/**
 * Get TinyMCE init options with dark mode support
 * @param {Object} options - Custom TinyMCE options
 * @returns {Object} - TinyMCE options with dark mode support
 */
export function getTinyMCEOptions(options = {}) {
    const isDarkMode = store.state.darkMode;
    
    // Default options with dark mode support
    const defaultOptions = {
        skin: isDarkMode ? 'oxide-dark' : 'oxide',
        content_css: isDarkMode ? 'dark' : 'default',
        setup: (editor) => {
            // Apply dark mode when editor is initialized
            editor.on('init', () => {
                applyDarkModeToEditor(editor);
                setupEditorDarkModeListener(editor);
            });
            
            // Call custom setup function if provided
            if (options.setup) {
                options.setup(editor);
            }
        }
    };
    
    // Merge default options with custom options
    return { ...defaultOptions, ...options, setup: defaultOptions.setup };
}
