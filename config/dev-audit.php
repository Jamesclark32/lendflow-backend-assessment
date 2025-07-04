<?php

declare(strict_types=1);

return [
    'settings' => [
        'always_lint' => false,
    ],
    'audits' => [
        [
            'title' => 'Tests',
            'command' => './vendor/bin/pest',
        ],
        [
            'title' => 'Test coverage',
            'command' => './vendor/bin/pest --coverage --min=80',
        ],
        [
            'title' => 'Type coverage',
            'command' => './vendor/bin/pest --type-coverage --min=100',
        ],
        [
            'title' => 'PHPStan type check',
            'command' => './vendor/bin/phpstan analyze -v --memory-limit=-1',
        ],
        [
            'title' => 'Pint PHP linting (dirty files only)',
            'command' => './vendor/bin/pint --dirty --test',
        ],
        [
            'title' => 'Rector code quality',
            'command' => './vendor/bin/rector --dry-run',
        ],
        [
            'title' => 'Peck spelling audit',
            'command' => './vendor/bin/peck',
        ],
        [
            'title' => 'Composer Audit',
            'command' => 'composer audit',
        ],
        [
            'title' => 'NPM Audit',
            'command' => 'npm audit',
        ],
    ],
    'linters' => [
        [
            'title' => 'Pint (dirty files only)',
            'command' => './vendor/bin/pint --dirty',
        ],
        [
            'title' => 'Rector',
            'command' => './vendor/bin/rector',
        ],
    ],
];
