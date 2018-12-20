<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\Component\Dotenv\Dotenv;

$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}

$result = require $file;

if (file_exists(__DIR__ . '/../.env')) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException('Add "symfony/dotenv" as a Composer dependency to load variables from a .env file.');
    }

    (new Dotenv())->load(__DIR__ . '/../.env');
}

return $result;
