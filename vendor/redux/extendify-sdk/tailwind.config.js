/**
 * The basics:
 * 1. purge will search classes and remove unused CSS when built in production mode
 * 2. important will add specificity. Setting this to true will enforce it. The current strategy
 *    should be fine as it is, but if someone else uses tailwind with 'important: true' and they don't scape
 *    it could interfere with our class names. If that becomes an issue (I think low chance...), we can add
 *    a prefix: https://tailwindcss.com/docs/configuration#prefix
 **/

module.exports = {
    // mode: 'jit',
    purge: ['src/**/*'],
    important: '.extendify-sdk',
    darkMode: false,
    theme: {
        screens: {
            xxs: '280px',
            xs: '480px',
            sm: '600px',
            md: '782px',
            md2: '960px', // admin sidebar auto folds
            lg: '1080px', // adminbar goes big
            xl: '1280px',
            '2xl': '1440px',
            '3xl': '1600px',
            '4xl': '1920px',
        },
        // Extend will add on to TW config, where the above will override and replace
        extend: {
            minWidth: {
                md2: '960px',
            },
            minHeight: {
                60: '15rem',
            },
            fontSize: {
                '3xl': ['2rem', '2.5rem'],
            },
            colors: {
                extendify: {
                    lightest: '#f8fffe',
                    light: '#e7f8f5',
                    main: '#008160',
                    link: '#299875',
                    bright: '#30a850',
                },
                'wp-theme': {
                    // It's a Tailwind convention for the base color to use 500 then build off that
                    500: 'var(--wp-admin-theme-color)',
                    600: 'var(--wp-admin-theme-color-darker-10)',
                    700: 'var(--wp-admin-theme-color-darker-20)',
                },
                wp: {
                    alert: {
                        yellow: '#f0b849',
                        red:    '#cc1818',
                        green:  '#4ab866',
                    },
                },
                gray: {
                    50:  '#fafafa',
                    100: '#f0f0f0',
                    150: '#eaeaea', // This wasn't a variable but I saw it on buttons
                    200: '#e0e0e0', // Used sparingly for light borders.
                    300: '#dddddd', // Used for most borders.
                    400: '#cccccc',
                    500: '#cccccc', // WP didn't have a 500 value for some reason so I just copied the 400
                    600: '#949494', // Meets 3:1 UI or large text contrast against white.
                    700: '#757575', // Meets 4.6:1 text contrast against white.
                    900: '#1e1e1e',
                },
            },
            zIndex: {
                high: '99999',
                max: '2147483647', // Highest the browser allows - don't block WP re-auth modal though
            },
        },
    },
    variants: {
        extend: {
            borderWidth: ['group-hover', 'hover', 'focus'],
            backgroundColor: ['active'],
            textColor: ['active'],
        },
    },
    plugins: [
        require('@tailwindcss/aspect-ratio'),
    ],
    corePlugins: {
        preflight: false,
        container: false,
    },
}
