/**
 * Dark Mode Mixin
 * 
 * This mixin provides helper methods for handling dark mode in Vue components
 */

export default {
    computed: {
        /**
         * Get text color classes for primary text
         * @returns {string} CSS classes
         */
        textPrimary() {
            return this.$store.state.darkMode ? 'text-white' : 'text-gray-900';
        },
        
        /**
         * Get text color classes for secondary text
         * @returns {string} CSS classes
         */
        textSecondary() {
            return this.$store.state.darkMode ? 'text-gray-300' : 'text-gray-700';
        },
        
        /**
         * Get text color classes for tertiary text
         * @returns {string} CSS classes
         */
        textTertiary() {
            return this.$store.state.darkMode ? 'text-gray-400' : 'text-gray-500';
        },
        
        /**
         * Get background color classes for primary background
         * @returns {string} CSS classes
         */
        bgPrimary() {
            return this.$store.state.darkMode ? 'bg-gray-900' : 'bg-white';
        },
        
        /**
         * Get background color classes for secondary background
         * @returns {string} CSS classes
         */
        bgSecondary() {
            return this.$store.state.darkMode ? 'bg-gray-800' : 'bg-gray-50';
        },
        
        /**
         * Get background color classes for tertiary background
         * @returns {string} CSS classes
         */
        bgTertiary() {
            return this.$store.state.darkMode ? 'bg-gray-700' : 'bg-gray-100';
        },
        
        /**
         * Get border color classes
         * @returns {string} CSS classes
         */
        borderColor() {
            return this.$store.state.darkMode ? 'border-gray-700' : 'border-gray-200';
        }
    },
    
    methods: {
        /**
         * Get dark mode classes based on options
         * @param {Object} options - Options for dark mode classes
         * @param {string} options.lightText - Light mode text color class
         * @param {string} options.darkText - Dark mode text color class
         * @param {string} options.lightBg - Light mode background color class
         * @param {string} options.darkBg - Dark mode background color class
         * @param {string} options.lightBorder - Light mode border color class
         * @param {string} options.darkBorder - Dark mode border color class
         * @param {string} options.lightHover - Light mode hover class
         * @param {string} options.darkHover - Dark mode hover class
         * @returns {string} Combined CSS classes
         */
        getDarkModeClasses(options = {}) {
            const isDarkMode = this.$store.state.darkMode;
            let classes = [];
            
            if (options.lightText && options.darkText) {
                classes.push(isDarkMode ? options.darkText : options.lightText);
            }
            
            if (options.lightBg && options.darkBg) {
                classes.push(isDarkMode ? options.darkBg : options.lightBg);
            }
            
            if (options.lightBorder && options.darkBorder) {
                classes.push(isDarkMode ? options.darkBorder : options.lightBorder);
            }
            
            if (options.lightHover && options.darkHover) {
                classes.push(isDarkMode ? options.darkHover : options.lightHover);
            }
            
            return classes.join(' ');
        }
    }
};
