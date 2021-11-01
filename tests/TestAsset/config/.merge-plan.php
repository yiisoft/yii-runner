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
        'events' => [
            '/' => [
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
