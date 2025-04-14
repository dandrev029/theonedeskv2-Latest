module.exports = {
    purge: [
        './resources/js/**/*.vue',
        './resources/scss/**/*.scss',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            // Define colors directly if needed, instead of using tailwindcss/colors
            colors: {
                // Custom colors for the new dashboard design
                'primary': {
                    50: '#f0fdf9',
                    100: '#dcfdf3',
                    200: '#baf8e7',
                    300: '#84f0d8',
                    400: '#4adec2',
                    500: '#22c6a7',
                    600: '#16a088',
                    700: '#138070',
                    800: '#14665a',
                    900: '#13544c',
                    950: '#07312c',
                },
                'secondary': {
                    50: '#f8f9fa',
                    100: '#f1f3f5',
                    200: '#e9ecef',
                    300: '#dee2e6',
                    400: '#ced4da',
                    500: '#adb5bd',
                    600: '#868e96',
                    700: '#495057',
                    800: '#343a40',
                    900: '#212529',
                },
            },
        },
    },
    variants: {
        extend: {},
    },
    plugins: [
        require('@tailwindcss/ui'),
        require('@tailwindcss/custom-forms'),
    ],
    future: {
        purgeLayersByDefault: true,
        removeDeprecatedGapUtilities: true,
    },
}
