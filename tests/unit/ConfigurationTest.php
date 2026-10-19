<?php

namespace HideInMatrix\TelegramUpload\Tests\unit;

use HideInMatrix\TelegramUpload\Configuration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    public function test_missing_configuration_disables_the_adapter(): void
    {
        self::assertFalse((new Configuration())->isConfigured());
    }

    public function test_valid_configuration_normalizes_the_base_url(): void
    {
        $configuration = new Configuration(['base_url' => 'https://disk.example.com/', 'chat_id' => '-100123456', 'api_key' => str_repeat('k', 32)]);
        self::assertTrue($configuration->isConfigured());
        self::assertSame('https://disk.example.com', $configuration->baseUrl());
    }

    #[DataProvider('invalidUrls')]
    public function test_invalid_base_urls_disable_the_adapter(string $url): void
    {
        self::assertFalse((new Configuration(['base_url' => $url, 'chat_id' => '-100123456', 'api_key' => str_repeat('k', 32)]))->isConfigured());
    }

    #[DataProvider('invalidKeys')]
    public function test_missing_short_and_header_unsafe_keys_disable_the_adapter(string $key): void
    {
        self::assertFalse((new Configuration(['base_url' => 'https://disk.example.com', 'chat_id' => '-100123456', 'api_key' => $key]))->isConfigured());
    }

    public static function invalidKeys(): array
    {
        return [[''], ['short'], [str_repeat('k', 31)], [str_repeat('k', 32)."\r\nInjected: header"]];
    }

    public static function invalidUrls(): array
    {
        return [['http://disk.example.com'], ['https://user:secret@disk.example.com'], ['https://disk.example.com?token=x'], ['https://disk.example.com/#key']];
    }
}
