<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

// Determine module root directory
$moduleRoot = dirname(__DIR__);

// CRITICAL: Load the module's vendor autoloader FIRST
// This contains the test class mappings from autoload-dev
$moduleAutoloader = $moduleRoot . '/vendor/autoload.php';
if (file_exists($moduleAutoloader)) {
    require_once $moduleAutoloader;
} else {
    // Fallback: Manually register namespace if module vendor doesn't exist
    // This happens in CI when only shop-level composer install runs

    // Register PSR-4 autoloader for src and test classes
    spl_autoload_register(function ($class) use ($moduleRoot) {
        // Handle src classes: Dantweb\OxidShopWatch\
        if (strpos($class, 'Dantweb\\OxidShopWatch\\Tests\\') === 0) {
            $relativeClass = str_replace('Dantweb\\OxidShopWatch\\Tests\\', '', $class);
            $file = $moduleRoot . '/tests/' . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        } elseif (strpos($class, 'Dantweb\\OxidShopWatch\\') === 0) {
            $relativeClass = str_replace('Dantweb\\OxidShopWatch\\', '', $class);
            $file = $moduleRoot . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
        return false;
    }, true, true); // Prepend to autoloader stack
}

// Try to find and load OXID shop bootstrap (which loads shop's vendor)
$possibleBootstraps = [
    // Standard module location: source/modules/osc/shop-watch
    $moduleRoot . '/../../../source/bootstrap.php',
    // Alternative: extensions directory
    $moduleRoot . '/../../source/bootstrap.php',
    // Absolute Docker path
    '/var/www/source/bootstrap.php',
    // GitHub Actions structure
    dirname(dirname(dirname($moduleRoot))) . '/source/bootstrap.php',
];

$bootstrapLoaded = false;
foreach ($possibleBootstraps as $shopBootstrap) {
    if (file_exists($shopBootstrap)) {
        require_once $shopBootstrap;
        $bootstrapLoaded = true;
        break;
    }
}

if (!$bootstrapLoaded) {
    throw new \RuntimeException(
        'Could not find OXID shop bootstrap.php. Module root: ' . $moduleRoot .
        '. Tried: ' . implode(', ', $possibleBootstraps)
    );
}
