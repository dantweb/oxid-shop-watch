<?php

declare(strict_types=1);

namespace Dantweb\OxidShopWatch\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Bootstrap tests to verify PHPUnit setup for ShopWatch module.
 *
 * @group unit
 * @group shopwatch
 * @group bootstrap
 */
class HelloWorldTest extends TestCase
{
    /**
     * @test
     * Verifies that the test namespace matches expected ShopWatch module namespace.
     */
    public function it_has_correct_namespace(): void
    {
        $expectedNamespace = 'Dantweb\OxidShopWatch\Tests\Unit';
        $actualNamespace = __NAMESPACE__;

        $this->assertEquals($expectedNamespace, $actualNamespace);
    }

    /**
     * @test
     * Verifies that OXID constants/classes are accessible (bootstrap.php loaded).
     */
    public function it_can_access_oxid_constants(): void
    {
        // This verifies bootstrap.php loaded and OXID is available
        $this->assertTrue(
            defined('OXID_PHP_UNIT') ||
            defined('OX_BASE_PATH') ||
            class_exists('OxidEsales\Eshop\Core\Registry', false),
            'OXID environment should be accessible via bootstrap'
        );
    }
}
