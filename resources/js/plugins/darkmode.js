import Vue from 'vue';
import store from '@/store';

// Global mixin for dark mode text color utilities
Vue.mixin({
    methods: {
        /**
         * Get text color class based on dark mode state
         * @param {string} lightModeColor - Tailwind color class for light mode (e.g., 'text-gray-700')
         * @param {string} darkModeColor - Tailwind color class for dark mode (e.g., 'text-gray-300')
         * @returns {Object} - Class binding object for Vue
         */
        getTextColor(lightModeColor, darkModeColor) {
            return {
                [lightModeColor]: !this.$store.state.darkMode,
                [darkModeColor]: this.$store.state.darkMode
            };
        },

        /**
         * Get CSS color variable based on dark mode state
         * @param {string} property - CSS property to set (e.g., 'color', 'background-color')
         * @param {string} lightValue - Value for light mode
         * @param {string} darkValue - Value for dark mode
         * @returns {Object} - Style binding object for Vue
         */
        getDarkModeStyle(property, lightValue, darkValue) {
            return {
                [property]: this.$store.state.darkMode ? darkValue : lightValue
            };
        },

        /**
         * Get text color style based on dark mode state using CSS variables
         * @param {string} type - Type of text (primary, secondary, tertiary)
         * @returns {Object} - Style binding object for Vue
         */
        getTextStyle(type = 'primary') {
            return {
                color: `var(--text-${type})`
            };
        },

        /**
         * Get background color class based on dark mode state
         * @param {string} lightModeBg - Tailwind background class for light mode (e.g., 'bg-white')
         * @param {string} darkModeBg - Tailwind background class for dark mode (e.g., 'bg-gray-800')
         * @returns {Object} - Class binding object for Vue
         */
        getBgColor(lightModeBg, darkModeBg) {
            return {
                [lightModeBg]: !this.$store.state.darkMode,
                [darkModeBg]: this.$store.state.darkMode
            };
        },

        /**
         * Get border color class based on dark mode state
         * @param {string} lightModeBorder - Tailwind border class for light mode (e.g., 'border-gray-200')
         * @param {string} darkModeBorder - Tailwind border class for dark mode (e.g., 'border-gray-700')
         * @returns {Object} - Class binding object for Vue
         */
        getBorderColor(lightModeBorder, darkModeBorder) {
            return {
                [lightModeBorder]: !this.$store.state.darkMode,
                [darkModeBorder]: this.$store.state.darkMode
            };
        },

        /**
         * Get combined color classes for an element (text, background, border, and more)
         * @param {Object} options - Configuration object
         * @param {string} options.lightText - Light mode text color
         * @param {string} options.darkText - Dark mode text color
         * @param {string} options.lightBg - Light mode background color
         * @param {string} options.darkBg - Dark mode background color
         * @param {string} options.lightBorder - Light mode border color
         * @param {string} options.darkBorder - Dark mode border color
         * @param {string} options.lightHover - Light mode hover color
         * @param {string} options.darkHover - Dark mode hover color
         * @param {string} options.lightFocus - Light mode focus color
         * @param {string} options.darkFocus - Dark mode focus color
         * @param {string} options.lightActive - Light mode active color
         * @param {string} options.darkActive - Dark mode active color
         * @param {string} options.lightPlaceholder - Light mode placeholder color
         * @param {string} options.darkPlaceholder - Dark mode placeholder color
         * @param {string} options.lightDivide - Light mode divide color
         * @param {string} options.darkDivide - Dark mode divide color
         * @param {string} options.lightRing - Light mode ring color
         * @param {string} options.darkRing - Dark mode ring color
         * @returns {Object} - Combined class binding object for Vue
         */
        getDarkModeClasses({
            lightText = null,
            darkText = null,
            lightBg = null,
            darkBg = null,
            lightBorder = null,
            darkBorder = null,
            lightHover = null,
            darkHover = null,
            lightFocus = null,
            darkFocus = null,
            lightActive = null,
            darkActive = null,
            lightPlaceholder = null,
            darkPlaceholder = null,
            lightDivide = null,
            darkDivide = null,
            lightRing = null,
            darkRing = null
        } = {}) {
            const classes = {};
            const isDarkMode = this.$store.state.darkMode;

            // Helper function to add classes conditionally
            const addClassPair = (light, dark) => {
                if (light && dark) {
                    classes[light] = !isDarkMode;
                    classes[dark] = isDarkMode;
                }
            };

            // Add all class pairs
            addClassPair(lightText, darkText);
            addClassPair(lightBg, darkBg);
            addClassPair(lightBorder, darkBorder);
            addClassPair(lightHover, darkHover);
            addClassPair(lightFocus, darkFocus);
            addClassPair(lightActive, darkActive);
            addClassPair(lightPlaceholder, darkPlaceholder);
            addClassPair(lightDivide, darkDivide);
            addClassPair(lightRing, darkRing);

            return classes;
        }
    }
});

// Add global computed properties for common dark mode classes
Vue.mixin({
    computed: {
        // Common text colors
        textPrimary() {
            return this.getTextColor('text-gray-900', 'text-white');
        },
        textSecondary() {
            return this.getTextColor('text-gray-700', 'text-gray-300');
        },
        textTertiary() {
            return this.getTextColor('text-gray-500', 'text-gray-400');
        },
        textMuted() {
            return this.getTextColor('text-gray-400', 'text-gray-500');
        },
        textError() {
            return this.getTextColor('text-red-600', 'text-red-400');
        },
        textSuccess() {
            return this.getTextColor('text-green-600', 'text-green-400');
        },
        textWarning() {
            return this.getTextColor('text-yellow-600', 'text-yellow-400');
        },
        textInfo() {
            return this.getTextColor('text-blue-600', 'text-blue-400');
        },
        textBrand() {
            return this.getTextColor('text-primary-600', 'text-primary-400');
        },

        // Common background colors
        bgPrimary() {
            return this.getBgColor('bg-white', 'bg-gray-800');
        },
        bgSecondary() {
            return this.getBgColor('bg-gray-50', 'bg-gray-700');
        },
        bgTertiary() {
            return this.getBgColor('bg-gray-100', 'bg-gray-600');
        },
        bgHighlight() {
            return this.getBgColor('bg-yellow-50', 'bg-yellow-900');
        },
        bgError() {
            return this.getBgColor('bg-red-50', 'bg-red-900');
        },
        bgSuccess() {
            return this.getBgColor('bg-green-50', 'bg-green-900');
        },
        bgWarning() {
            return this.getBgColor('bg-yellow-50', 'bg-yellow-900');
        },
        bgInfo() {
            return this.getBgColor('bg-blue-50', 'bg-blue-900');
        },
        bgBrand() {
            return this.getBgColor('bg-primary-50', 'bg-primary-900');
        },

        // Common border colors
        borderPrimary() {
            return this.getBorderColor('border-gray-200', 'border-gray-700');
        },
        borderSecondary() {
            return this.getBorderColor('border-gray-300', 'border-gray-600');
        },
        borderHighlight() {
            return this.getBorderColor('border-yellow-200', 'border-yellow-700');
        },
        borderError() {
            return this.getBorderColor('border-red-200', 'border-red-700');
        },
        borderSuccess() {
            return this.getBorderColor('border-green-200', 'border-green-700');
        },
        borderWarning() {
            return this.getBorderColor('border-yellow-200', 'border-yellow-700');
        },
        borderInfo() {
            return this.getBorderColor('border-blue-200', 'border-blue-700');
        },
        borderBrand() {
            return this.getBorderColor('border-primary-200', 'border-primary-700');
        },

        // Common CSS variable styles
        textPrimaryStyle() {
            return this.getTextStyle('primary');
        },
        textSecondaryStyle() {
            return this.getTextStyle('secondary');
        },
        textTertiaryStyle() {
            return this.getTextStyle('tertiary');
        }
    }
});
