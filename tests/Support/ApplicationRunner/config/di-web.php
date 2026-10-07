<?php

declare(strict_types=1);

/** @var array $params */

return [
    stdClass::class => static function () use ($params): stdClass {
        $stdClass = new stdClass();
        $stdClass->name = $params['name'];
        $stdClass->age = $params['age'];
        return $stdClass;
    },
];
