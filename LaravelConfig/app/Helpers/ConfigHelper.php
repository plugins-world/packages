<?php

namespace Plugins\LaravelConfig\Helpers;

use Illuminate\Support\Facades\Cache;
use Plugins\LaravelConfig\Models\Config;

class ConfigHelper
{
    public static function fresnsConfigDefaultLangTag()
    {
        return app()->getLocale();
    }

    // Get config developer mode
    public static function fresnsConfigDeveloperMode(): array
    {
        $developerMode = Cache::rememberForever('developer_mode', function () {
            $itemData = Config::where('item_key', 'developer_mode')->first();

            return $itemData?->item_value;
        });

        $developerModeArr = [
            'cache' => $developerMode['cache'] ?? true,
            'apiSignature' => $developerMode['apiSignature'] ?? true,
        ];

        return $developerModeArr;
    }

    public static function getConfigKeyCacheKey(string $itemKey, ?string $langTag = null)
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_config_{$itemKey}_{$langTag}";

        return $cacheKey;
    }

    // Get config value based on Key
    public static function fresnsConfigByItemKey(string $itemKey, ?string $itemTag = null, array $where = [], ?string $langTag = null): mixed
    {
        $cacheKey = ConfigHelper::getConfigKeyCacheKey($itemKey, $langTag);
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $itemValue = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($itemValue)) {
            if ($itemTag) {
                $where['item_tag'] = $itemTag;
            }

            $itemData = Config::where('item_key', $itemKey)->where($where)->first();
            if ($itemData) {
                // $itemValue = $itemData->is_multilingual ? LanguageHelper::fresnsLanguageByTableKey($itemData->item_key, $itemData->item_type, $langTag) : $itemData->item_value;
                $itemValue = $itemData->item_value;
            }

            CacheHelper::put($itemValue, $cacheKey, $cacheTag);
        }

        return $itemValue;
    }

    public static function getConfigKeysCacheKey(array $itemKeys, ?string $langTag = null)
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $key = reset($itemKeys).'_'.end($itemKeys).'_'.count($itemKeys);
        $cacheKey = "fresns_config_keys_{$key}_{$langTag}";

        return $cacheKey;
    }

    // Get multiple values based on multiple keys
    public static function fresnsConfigByItemKeys(array $itemKeys, ?string $itemTag = null, array $where = [], ?string $langTag = null): array
    {
        $cacheKey = ConfigHelper::getConfigKeysCacheKey($itemKeys, $langTag);

        $keysData = CacheHelper::get($cacheKey, 'fresnsConfigs');

        if (empty($keysData)) {
            $keysData = [];
            foreach ($itemKeys as $itemKey) {
                // Loop query once and place them in the cache separately
                $keysData[$itemKey] = ConfigHelper::fresnsConfigByItemKey($itemKey, $itemTag, $where, $langTag);
            }

            CacheHelper::put($keysData, $cacheKey, 'fresnsConfigs');
        }

        return $keysData;
    }
}