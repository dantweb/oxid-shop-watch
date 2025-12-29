<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use Dantweb\OxidShopWatch\Controller\AssumptionController;

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id' => 'dantweb_shop_watch',
    'title' => [
        'de' => 'Shop Watch - E2E Testing API',
        'en' => 'Shop Watch - E2E Testing API',
    ],
    'description' => [
        'de' => 'HTTP-API zur Datenbankstatusvalidierung für E2E-Tests',
        'en' => 'HTTP API for database state verification in E2E tests',
    ],
    'thumbnail' => 'img/shop_watch_logo.png',
    'version' => '1.0.0',
    'author' => 'Dantweb',
    'url' => 'https://github.com/dantweb/oxid-shop-watch',
    'email' => '',
    'extend' => [],
    'controllers' => [
        'shopwatch_assumption' => AssumptionController::class,
    ],
    'events' => [],
    'templates' => [],
    'settings' => [
        ['group' => 'SHOPWATCH', 'name' => 'shopwatchEnabled', 'type' => 'bool', 'value' => '0', 'position' => 10],
        ['group' => 'SHOPWATCH', 'name' => 'shopwatchAllowedHosts', 'type' => 'arr', 'value' => '[]', 'position' => 20],
        ['group' => 'SHOPWATCH', 'name' => 'shopwatchRateLimitEnabled', 'type' => 'bool', 'value' => '0', 'position' => 30],
        ['group' => 'SHOPWATCH', 'name' => 'shopwatchRateLimitPerMinute', 'type' => 'str', 'value' => '100', 'position' => 40],
    ]
];
