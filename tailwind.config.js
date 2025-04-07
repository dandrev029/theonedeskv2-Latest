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
                // You can add custom colors here
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
