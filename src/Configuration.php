<?php

namespace HideInMatrix\TelegramUpload;

use InvalidArgumentException;

final class Configuration
{
    public const MAX_BYTES = 10 * 1024 * 1024;

    public function __construct(private readonly array $values = []) {}

    public function baseUrl(): string
    {
        $url = rtrim((string) ($this->values['base_url'] ?? ''), '/');
        $parts = parse_url($url);

        if (!$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new InvalidArgumentException('Configure an HTTPS tg-disk base_url without credentials, query or fragment.');
        }

        return $url;
    }

    public function chatId(): string
    {
        $id = (string) ($this->values['chat_id'] ?? '');
        if (!preg_match('/^-?[0-9]+$/D', $id)) {
            throw new InvalidArgumentException('Configure the Telegram numeric chat_id.');
        }

        return $id;
    }

    public function isConfigured(): bool
    {
        try {
            $this->baseUrl();
            $this->chatId();

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public static function validFileId(mixed $id): bool
    {
        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{6,255}$/D', $id) === 1;
    }
}
