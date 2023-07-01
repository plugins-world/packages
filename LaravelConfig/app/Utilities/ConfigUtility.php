<?php

namespace Plugins\LaravelConfig\Utilities;

use Plugins\LaravelConfig\Models\Config;
use Plugins\LaravelConfig\Helpers\ConfigHelper;

class ConfigUtility
{
    public static function updateConfigs(array $keys = [], ?string $itemTag = null, array $where = [])
    {
        $itemKeys = $keys;

        foreach ($itemKeys as $itemKey) {
            if ($itemTag) {
                $where['item_tag'] = $itemTag;
            }

            $config = Config::findConfig($itemKey, $itemTag);
            if (!$config) {
                continue;
            }

            $config->item_value = \request($config->item_key);
            $config->save();

            Config::forgetCache($config->item_key);
        }

        $result = ConfigHelper::fresnsConfigByItemKeys($itemKeys, $itemTag);

        return $result;
    }

    public static function addFresnsConfigItems(array $fresnsConfigItems): void
    {
        foreach ($fresnsConfigItems as $item) {
            $config = Config::where('item_key', $item['item_key'])->first();
            if (empty($config)) {
                Config::create($item);

                if ($item['is_multilingual'] ?? null) {
                    $fresnsLangItems = [
                        'table_name' => 'configs',
                        'table_column' => 'item_value',
                        'table_id' => null,
                        'table_key' => $item['item_key'],
                        'language_values' => $item['language_values'] ?? [],
                    ];

                    ConfigUtility::changeFresnsLanguageItems($fresnsLangItems);
                }
            }
        }
    }

    // remove config items
    public static function removeFresnsConfigItems(array $fresnsConfigKeys): void
    {
        foreach ($fresnsConfigKeys as $item) {
            $config = Config::findConfig($item['item_key'], $item['item_tag']);

            if ($config?->is_custom == 1 && $config?->is_multilingual == 1) {
                // Language::where('table_name', 'configs')->where('table_column', 'item_value')->where('table_key', $key)->forceDelete();
            }

            $config?->forceDelete();

            Config::forgetCache($config?->item_key);
        }
    }

    // change config items
    public static function changeFresnsConfigItems(array $fresnsConfigItems): void
    {
        foreach ($fresnsConfigItems as $item) {
            Config::updateOrCreate([
                'item_key' => $item['item_key'],
            ],
                collect($item)->only('item_key', 'item_value', 'item_type', 'item_tag', 'is_multilingual', 'is_api')->toArray()
            );

            if ($item['is_multilingual'] ?? null) {
                $fresnsLangItems = [
                    'table_name' => 'configs',
                    'table_column' => 'item_value',
                    'table_id' => null,
                    'table_key' => $item['item_key'],
                    'language_values' => $item['language_values'] ?? [],
                ];

                ConfigUtility::changeFresnsLanguageItems($fresnsLangItems);
            }

            Config::forgetCache($item['item_key']);
        }
    }

    // change language items
    public static function changeFresnsLanguageItems($fresnsLangItems): void
    {
        foreach ($fresnsLangItems['language_values'] ?? [] as $key => $value) {
            $item = $fresnsLangItems;
            $item['lang_tag'] = $key;
            $item['lang_content'] = $value;

            // use collect()->only() instead of unset($item['language_values'])
            // unset($item['language_values']);

            // Language::updateOrCreate($item);
        }
    }
}