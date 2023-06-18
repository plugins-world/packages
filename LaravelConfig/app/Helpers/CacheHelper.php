<?php

namespace Plugins\LaravelConfig\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    const NULL_CACHE_KEY_PREFIX = 'null_key_';
    const NULL_CACHE_COUNT = 2;

    // cache time
    public static function fresnsCacheTimeByFileType(?int $fileType = null, ?int $minutes = null): Carbon
    {
        $digital = rand(12, 72);

        return now()->addHours($digital);
    }

    // get null cache key
    public static function getNullCacheKey(string $cacheKey): string
    {
        return CacheHelper::NULL_CACHE_KEY_PREFIX.$cacheKey;
    }

    /**
     * forget fresns key.
     */
    public static function forgetFresnsKey(string $cacheKey, mixed $cacheTags = null): void
    {
        $nullCacheKey = CacheHelper::getNullCacheKey($cacheKey);
        $nullCacheTag = ['fresnsNullCount'];

        if (Cache::supportsTags() && $cacheTags) {
            $cacheTags = (array) $cacheTags;

            Cache::tags($cacheTags)->forget($cacheKey);
            Cache::tags($nullCacheTag)->forget($nullCacheKey);
        } else {
            Cache::forget($cacheKey);
            Cache::forget($nullCacheKey);
        }
    }

    /**
     * forget fresns config keys.
     */
    public static function forgetFresnsConfigByItemKeys(array $itemKeys, ?string $itemTag = null, ?string $langTag = null)
    {
        foreach ($itemKeys as $itemKey) {
            $itemKeyCacheKey = ConfigHelper::getConfigKeyCacheKey($itemKey, $langTag);
            CacheHelper::forgetFresnsKey($itemKeyCacheKey, '$fresnsConfigs');
        }

        $itemKeysCacheKey = ConfigHelper::getConfigKeysCacheKey($itemKeys, $langTag);
        CacheHelper::forgetFresnsKey($itemKeysCacheKey, 'fresnsConfigs');
    }

    // put null cache count
    public static function putNullCacheCount(string $cacheKey, ?int $cacheMinutes = null): void
    {
        CacheHelper::forgetFresnsKey($cacheKey);

        $nullCacheKey = CacheHelper::getNullCacheKey($cacheKey);
        $cacheTag = 'fresnsNullCount';

        $currentCacheKeyNullNum = (int) CacheHelper::get($nullCacheKey, $cacheTag) ?? 0;

        $now = $cacheMinutes ? now()->addMinutes($cacheMinutes) : CacheHelper::fresnsCacheTimeByFileType();

        if (Cache::supportsTags()) {
            $cacheTags = (array) $cacheTag;

            Cache::tags($cacheTags)->put($nullCacheKey, ++$currentCacheKeyNullNum, $now);
        } else {
            Cache::put($nullCacheKey, ++$currentCacheKeyNullNum, $now);
        }

        CacheHelper::addCacheItems($cacheKey, $cacheTag);
    }

    // add cache items
    public static function addCacheItems(string $cacheKey, mixed $cacheTags = null): void
    {
        if (empty($cacheTags)) {
            return;
        }

        $cacheTags = (array) $cacheTags;
        $tags = [
            'fresnsNullCount',
        ];

        foreach ($cacheTags as $tag) {
            if (in_array($tag, $tags)) {
                $cacheItems = Cache::get($tag) ?? [];

                $datetime = date('Y-m-d H:i:s');

                $newCacheItems = Arr::add($cacheItems, $cacheKey, $datetime);

                Cache::forever($tag, $newCacheItems);
            }
        }
    }

    // is known to be empty
    public static function isKnownEmpty(string $cacheKey): bool
    {
        $whetherToCache = ConfigHelper::fresnsConfigDeveloperMode()['cache'];
        $isWebCache = Str::startsWith($cacheKey, 'fresns_web');
        if (! $whetherToCache && ! $isWebCache) {
            return false;
        }

        $nullCacheKey = CacheHelper::getNullCacheKey($cacheKey);

        $nullCacheCount = CacheHelper::get($nullCacheKey, 'fresnsNullCount');

        // null cache count
        if ($nullCacheCount > CacheHelper::NULL_CACHE_COUNT) {
            return true;
        }

        return false;
    }

    // cache put
    public static function put(mixed $cacheData, string $cacheKey, mixed $cacheTags = null, ?int $nullCacheMinutes = null, ?Carbon $cacheTime = null): void
    {
        $whetherToCache = ConfigHelper::fresnsConfigDeveloperMode()['cache'];
        $isWebCache = Str::startsWith($cacheKey, 'fresns_web');
        if (! $whetherToCache && ! $isWebCache) {
            return;
        }

        $cacheTags = (array) $cacheTags;

        // null cache count
        if (empty($cacheData)) {
            CacheHelper::putNullCacheCount($cacheKey, $nullCacheMinutes);

            return;
        }

        $cacheTime = $cacheTime ?: CacheHelper::fresnsCacheTimeByFileType();

        if (Cache::supportsTags() && $cacheTags) {
            Cache::tags($cacheTags)->put($cacheKey, $cacheData, $cacheTime);
        } else {
            Cache::put($cacheKey, $cacheData, $cacheTime);
        }

        CacheHelper::addCacheItems($cacheKey, $cacheTags);

        $cacheTagList = Cache::get('fresns_cache_tags') ?? [];
        foreach ($cacheTags as $tag) {
            $datetime = date('Y-m-d H:i:s');

            $newTagList = Arr::add($cacheTagList, $tag, $datetime);

            Cache::forever('fresns_cache_tags', $newTagList);
        }
    }

    // cache get
    public static function get(string $cacheKey, mixed $cacheTags = null): mixed
    {
        $whetherToCache = ConfigHelper::fresnsConfigDeveloperMode()['cache'];
        $isWebCache = Str::startsWith($cacheKey, 'fresns_web');
        if (! $whetherToCache && ! $isWebCache) {
            return null;
        }

        $cacheTags = (array) $cacheTags;
        if (Cache::supportsTags() && $cacheTags) {
            return Cache::tags($cacheTags)->get($cacheKey);
        }

        return Cache::get($cacheKey);
    }
}