<?php

namespace Plugins\LaravelConfig\Models\Traits;

use Plugins\LaravelConfig\Models\Config;

/**
 * @mixin Config
 */
trait ConfigServiceTrait
{
    public static function findConfig(string $itemKey = null, ?string $itemTag, array $where = []): null|Config
    {
        if (empty($itemKey)) {
            return null;
        }
        
        if ($itemKey) {
            $where['item_key'] = $itemKey;
        }

        if ($itemTag) {
            $where['item_tag'] = $itemTag;
        }

        return Config::query()->where($where)->first();
    }
}