import js from '@eslint/js';

const browserGlobals = Object.fromEntries(
    [
        'console',
        'document',
        'window',
        'fetch',
        'URL',
        'Element',
        'Event',
        'HTMLButtonElement',
        'Image',
        'IntersectionObserver',
        'MutationObserver',
        'ResizeObserver',
    ].map((name) => [name, 'readonly']),
);

export default [
    {
        ignores: ['public/**', 'node_modules/**', 'vendor/**', 'storage/**', 'bootstrap/cache/**'],
    },
    js.configs.recommended,
    {
        files: ['**/*.js'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: browserGlobals,
        },
        rules: {
            'no-console': ['warn', { allow: ['warn', 'error'] }],
            'no-unused-vars': 'warn',
            eqeqeq: ['error', 'always'],
            curly: ['error', 'all'],
            semi: ['error', 'always'],
            quotes: ['error', 'single', { avoidEscape: true }],
        },
    },
];
