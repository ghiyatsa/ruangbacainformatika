export default {
    extends: ['@commitlint/config-conventional'],
    rules: {
        'scope-enum': [
            1,
            'always',
            [
                'admin',
                'auth',
                'catalog',
                'deps',
                'github',
                'kiosk',
                'loan',
                'settings',
                'similarity',
                'ui',
                'return',
                'whatsapp',
            ],
        ],
        'type-enum': [
            2,
            'always',
            [
                'build',
                'chore',
                'ci',
                'docs',
                'feat',
                'fix',
                'perf',
                'refactor',
                'revert',
                'test',
            ],
        ],
    },
};
