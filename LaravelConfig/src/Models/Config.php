<?php

namespace MouYong\LaravelConfig\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use MouYong\LaravelConfig\Models\Config as ModelsConfig;

class Config extends Model
{
    const CACHE_KEY_PREFIX = 'item_key:';
    const CACHE_KEY_MINUTES = 60;

    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_multilingual' => 'bool',
        'is_api' => 'bool',
        'is_custom' => 'bool',
        'is_enable' => 'bool',
    ];

    public static function findConfig(?string $itemKey = null, ?string $itemTag, array $where = [])
    {
        if ($itemKey) {
            $where['item_key'] = $itemKey;
        }

        if ($itemTag) {
            $where['item_tag'] = $itemTag;
        }

        return Config::query()
            ->where($where)
            ->first();
    }

    public static function updateConfigs(array $keys = [], ?string $itemTag = null)
    {
        $itemKeys = $keys;

        foreach ($itemKeys as $itemKey) {
            $config = Config::findConfig($itemKey, $itemTag);
            if (!$config) {
                continue;
            }

            $config->item_value = \request($config->item_key);
            $config->save();

            Config::forgetCache($itemKey);
        }

        $result = Config::getValueByKeys($itemKeys, $itemTag);

        return $result;
    }

    public function getItemValueDescAttribute()
    {
        if (in_array($this->item_type, ['array', 'json', 'object'])) {
            $value = json_decode($this->item_value, true) ?: [];
        } else if (in_array($this->item_type, ['bool', 'boolean'])) {
            $value = filter_var($this->item_value, FILTER_VALIDATE_BOOLEAN);
        } else if ($this->item_type === 'string') {
            $value = strval($this->item_value);
        } else if ($this->item_type === 'number') {
            $value = intval($this->item_value);
        } else {
            $value = $this->item_value;
        }

        return $value;
    }

    public function getDetail()
    {
        return [
            'id' => $this->id,
            'item_key' => $this->item_key,
            'item_type' => $this->item_type,
            'item_value' => $this->item_value,
            'item_value_desc' => $this->item_value_desc,
        ];
    }

    public static function addKeyValue(string $itemTag, string $itemKey, string $itemType, mixed $itemValue)
    {
        if ($itemType === 'string') {
            $itemValue = strval($itemValue);
        }

        if ($itemType === 'number') {
            $itemValue = intval($itemValue);
        }

        if (in_array($itemType, ['bool', 'boolean'])) {
            $itemValue = filter_var($itemValue, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        }

        if (in_array($itemType, ['array', 'json', 'object']) || is_array($itemValue)) {
            if (!is_string($itemValue)) {            
                $itemValue = json_encode($itemValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            }
        }

        Config::forgetCache($itemKey);

        return Config::updateOrCreate([
            'item_tag' => $itemTag,
            'item_key' => $itemKey,
        ], [
            'item_type' => $itemType,
            'item_value' => $itemValue,
        ]);
    }

    public static function addConfig(array $item)
    {
        return static::addKeyValue(
            $item['item_tag'],
            $item['item_key'],
            $item['item_type'],
            $item['item_value']
        );
    }

    public static function addKeyValues(array $itemKeyItemValues)
    {
        return array_map(function ($item) {
            return static::addConfig($item);
        }, $itemKeyItemValues);
    }

    public static function removeKey(string $itemTag, string $itemKey)
    {
        Config::forgetCache($itemKey);

        return Config::query()
            ->where('item_tag', $itemTag)
            ->where('item_key', $itemKey)
            ->forceDelete();
    }

    public static function removeConfig(array $item)
    {
        return static::removeKey(
            $item['item_tag'],
            $item['item_key']
        );
    }

    public static function removeKeyValues(array $itemKeyItemValues)
    {
        return array_map(function ($item) {
            return static::removeConfig($item);
        }, $itemKeyItemValues);
    }

    public static function setStringValue(string $tag, string $key, ?string $value = null)
    {
        return static::addKeyValue($tag, $key, (string) $value, 'string');
    }

    public static function setBoolValue(string $tag, string $key, bool $value = false)
    {
        return static::addKeyValue($tag, $key, (bool) $value, 'bool');
    }

    public static function setJsonValue(string $tag, string $key, ?array $value = [])
    {
        return static::addKeyValue($tag, $key, json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK), 'json');
    }

    public static function getValueByKey(string $itemKey, ?string $itemTag = null, array $where = [])
    {
        $cacheKey = Config::CACHE_KEY_PREFIX . $itemKey;
        $cacheTime = now()->addMinutes(ModelsConfig::CACHE_KEY_MINUTES);

        $config = Cache::remember($cacheKey, $cacheTime, function () use ($itemKey, $itemTag, $where) {
            $where['item_key'] = $itemKey;
            if ($itemTag) {
                $where['item_tag'] = $itemTag;
            }
            
            return Config::query()->where($where)->first();
        });

        if (is_null($config)) {
            return Cache::pull($cacheKey);
        }

        return $config->item_value_desc;
    }

    public static function getValueByKeys(array $itemKeys, ?string $itemTag = null, array $where = []): array
    {
        $data = [];

        foreach ($itemKeys as $index => $itemKey) {
            $value = Cache::get($itemKey);
            if ($value) {
                unset($itemKeys[$index]);

                $data[$itemKey] = $value;
            }
        }

        // 所有数据已全部查出
        if (count($data) === count($itemKeys)) {
            return $data;
        }

        // 拼接额外查询条件
        if ($itemTag) {
            $where['item_tag'] = $itemTag;
        }
        
        // 已查出部分缓存中的 key，还有部分 key 需要查询
        $values = Config::query()
            ->whereIn('item_key', $itemKeys)
            ->where($where)
            ->get();

        foreach ($itemKeys as $index => $itemKey) {
            $cacheKey = Config::CACHE_KEY_PREFIX . $itemKey;
            $cacheTime = now()->addMinutes(ModelsConfig::CACHE_KEY_MINUTES);

            $itemValue = Cache::remember($cacheKey, $cacheTime, function () use ($values, $itemKey) {
                return collect($values)->where('item_key', $itemKey)->first();
            });

            if (is_null($itemValue)) {
                Cache::pull($cacheKey);
            }

            $data[$itemKey] = $itemValue?->item_value_desc;
        }

        return $data;
    }

    public static function forgetCache(string $itemKey)
    {
        $cacheKey = Config::CACHE_KEY_PREFIX . $itemKey;
        Cache::forget($cacheKey);
    }
}
