<?php

use MouYong\LaravelConfig\Models\Config;

if (! function_exists('db_config')) {
    function db_config($itemKey = null): mixed
    {
        $configModel = config('laravel-config.config_model', Config::class);
        
        if (is_string($itemKey)) {
            return $configModel::getItemValueByItemKey($itemKey);
        }

        if (is_array($itemKey)) {
            return $configModel::getValueByKeys($itemKey);
        }

        return new $configModel();
    }
}

if (! function_exists('db_config_central')) {
    function db_config_central($itemKey = null): mixed
    {
        if (! function_exists('tenancy')) {
            return db_config($itemKey);
        }

        return central(function ($tenant) use ($itemKey) {
            return db_config($itemKey);
        });
    }
}

if (! function_exists('central')) {
    function central(callable $callable): mixed
    {
        if (! function_exists('tenancy')) {            
            $tenant = null;

            return $callable($tenant);
        }

        return tenancy()->central(function ($tenant) use ($callable) {
            return $callable($tenant);
        });
    }
}
