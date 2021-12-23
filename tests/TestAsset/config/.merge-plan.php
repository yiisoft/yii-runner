<?php

declare(strict_types=1);

// Do not edit. Content will be replaced.
return [
    '/' => [
        'params' => [
            '/' => [
                'params.php',
            ],
        ],
        'beforeEvents' => [
            '/' => [
                'before-events.php',
            ],
        ],
        'events' => [
            '/' => [
                '$beforeEvents',
                'events.php',
            ],
        ],
        'events-console' => [
            '/' => [
                '$events',
                'events-console.php',
            ],
        ],
        'events-web' => [
            '/' => [
                '$events',
                'events-web.php',
            ],
        ],
    ],
];
