<?php

namespace HideInMatrix\TelegramUpload\Providers;

use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Foundation\Config;
use FoF\Upload\Commands\DownloadHandler;
use FoF\Upload\Processors\ImageProcessor;
use GuzzleHttp\Client;
use HideInMatrix\TelegramUpload\Client\TgDiskClient;
use HideInMatrix\TelegramUpload\Configuration;
use HideInMatrix\TelegramUpload\Downloaders\TelegramDownloader;
use HideInMatrix\TelegramUpload\Processors\PreserveImageProcessor;

final class TelegramServiceProvider extends AbstractServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(Configuration::class, function ($container) {
            $config = $container->make(Config::class);
            $values = $config['telegram-upload'] ?? [];

            return new Configuration(array_replace([
                'base_url' => getenv('TG_DISK_BASE_URL') ?: '',
                'chat_id' => getenv('TG_DISK_CHAT_ID') ?: '',
                'api_key' => getenv('TG_DISK_API_KEY') ?: '',
            ], is_array($values) ? $values : []));
        });
        $this->container->singleton(TgDiskClient::class, function ($container) {
            return new TgDiskClient(new Client(), $container->make(Configuration::class));
        });
        $this->container->extend(ImageProcessor::class, function ($processor, $container) {
            return $container->make(PreserveImageProcessor::class);
        });
    }

    public function boot(): void
    {
        // Must run before FoF's catch-all DefaultDownloader.
        DownloadHandler::prependDownloader($this->container->make(TelegramDownloader::class));
    }
}
