<?php

namespace Plugins\LaravelConfig\Helpers;

use Plugins\LaravelConfig\Models\Config;
use Plugins\MarketManager\Utils\LaravelCache;

class ConfigHelper
{
    // Get config default langTag
    public static function fresnsConfigDefaultLangTag()
    {
        return app()->getLocale();
    }

    // Get config developer mode
    public static function fresnsConfigDeveloperMode(): array
    {
        $developerMode = LaravelCache::rememberForever('developer_mode', function () {
            $itemData = Config::where('item_key', 'developer_mode')->first();

            return $itemData?->item_value;
        });

        $developerModeArr = [
            'cache' => $developerMode['cache'] ?? true,
            'apiSignature' => $developerMode['apiSignature'] ?? true,
        ];

        return $developerModeArr;
    }

    // Get config value based on Key
    public static function fresnsConfigByItemKey(string $itemKey, ?string $itemTag = null, array $where = [], ?string $langTag = null): mixed
    {
        return Config::getValueByKey($itemKey, $itemTag, $where);
    }

    // Get multiple values based on multiple keys
    public static function fresnsConfigByItemKeys(array $itemKeys, ?string $itemTag = null, array $where = [], ?string $langTag = null): array
    {
        return Config::getValueByKeys($itemKeys, $itemTag, $where);
    }
}