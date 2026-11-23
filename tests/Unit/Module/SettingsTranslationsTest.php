<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Dantweb\OxidShopWatch\Tests\Unit\Module;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The classic admin shows a module setting with the text of `SHOP_MODULE_<name>` and its group with
 * `SHOP_MODULE_GROUP_<group>` (views/admin_twig/<language>/*_lang.php). Without them it shows the raw key.
 */
final class SettingsTranslationsTest extends TestCase
{
    private const MODULE = __DIR__ . '/../../..';

    /** @return iterable<string, array{string}> */
    public static function languages(): iterable
    {
        yield 'German' => ['de'];
        yield 'English' => ['en'];
    }

    #[DataProvider('languages')]
    public function testEveryGroupAndSettingHasATextInEachLanguage(string $language): void
    {
        $texts = $this->texts($language);

        foreach ($this->settings() as $setting) {
            foreach (['SHOP_MODULE_GROUP_' . $setting['group'], 'SHOP_MODULE_' . $setting['name']] as $key) {
                self::assertNotSame('', trim((string) ($texts[$key] ?? '')), "$language: $key");
            }
        }
    }

    /** @return list<array{group: string, name: string}> */
    private function settings(): array
    {
        $aModule = [];
        include self::MODULE . '/metadata.php';
        self::assertNotEmpty($aModule['settings'] ?? []);

        return $aModule['settings'];
    }

    /** @return array<string, string> */
    private function texts(string $language): array
    {
        $texts = [];
        foreach (glob(self::MODULE . "/views/admin_twig/$language/*_lang.php") ?: [] as $file) {
            $aLang = [];
            include $file;
            $texts += $aLang;
        }

        return $texts;
    }
}
