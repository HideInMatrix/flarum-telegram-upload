<?php

namespace HideInMatrix\TelegramUpload\Tests\unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use HideInMatrix\TelegramUpload\Client\TgDiskClient;
use HideInMatrix\TelegramUpload\Configuration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TgDiskClientTest extends TestCase
{
    private function client(MockHandler $mock, array &$history): TgDiskClient
    {
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        return new TgDiskClient(new Client(['handler' => $stack]), new Configuration([
            'base_url' => 'https://disk.example.com', 'chat_id' => '-100123456',
        ]));
    }

    public function test_upload_sends_the_original_bytes_as_a_document(): void
    {
        $history = [];
        $mock = new MockHandler([new Response(200, [], '{"code":200,"data":{"file_id":"test_file_id","file_size":7}}')]);
        $bytes = "GIF\0\xff\x01x";
        $client = $this->client($mock, $history);
        self::assertSame('test_file_id', $client->upload('fixture', $bytes, strlen($bytes)));
        $request = $history[0]['request'];
        self::assertSame('https://disk.example.com/api/telegram/send', (string) $request->getUri());
        self::assertStringContainsString('filename="fixture.bin"', (string) $request->getBody());
        self::assertStringContainsString($bytes, (string) $request->getBody());
        self::assertFalse($history[0]['options']['allow_redirects']);
    }

    #[DataProvider('badResponses')]
    public function test_failed_uploads_do_not_return_a_successful_file_id(string $json): void
    {
        $history = [];
        $client = $this->client(new MockHandler([new Response(200, [], $json)]), $history);
        $this->expectException(\Throwable::class);
        $client->upload('fixture', 'x', 1);
    }

    public static function badResponses(): array
    {
        return [
            ['{"code":500,"data":null}'],
            ['{"code":200,"data":{"file_id":"../../escape","file_size":1}}'],
            ['{"code":200,"data":{"file_id":"test_file_id","file_size":2}}'],
            ['not-json'],
        ];
    }

    public function test_oversized_upload_is_rejected_before_any_request(): void
    {
        $history = [];
        $mock = new MockHandler([new Response(200)]);
        try {
            $this->client($mock, $history)->upload('fixture', 'x', Configuration::MAX_BYTES + 1);
            self::fail('Oversize was accepted');
        } catch (\RuntimeException) {
            self::assertSame([], $history);
            self::assertCount(1, $mock);
        }
    }
}
