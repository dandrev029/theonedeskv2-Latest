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
                    50: '#edfcf9',
                    100: '#d0f7f0',
                    200: '#a5efe3',
                    300: '#6de0d1',
                    400: '#3cc8ba',
                    500: '#18A5A7',
                    600: '#158a8c',
                    700: '#166f71',
                    800: '#175a5c',
                    900: '#184a4c',
                    950: '#042f32',
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
