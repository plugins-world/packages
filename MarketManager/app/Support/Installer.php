<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Plugins\MarketManager\Support;

use MouYong\LaravelConfig\Models\Config;

class Installer
{
    protected $config = [
        // [
        //     'item_tag' => 'market_manager',
        //     'item_key' => 'access_key',
        //     'item_type' => 'string',
        //     'item_value' => null,
        // ],
        [
            'item_tag' => 'market_manager',
            'item_key' => 'market_server_host',
            'item_type' => 'string',
            'item_value' => 'https://market.plugins-world.cn',
        ],
        [
            'item_tag' => 'market_manager',
            'item_key' => 'system_url',
            'item_type' => 'string',
            'item_value' => null,
        ],
        [
            'item_tag' => 'market_manager',
            'item_key' => 'install_datetime',
            'item_type' => 'string',
            'item_value' => null,
        ],
        [
            'item_tag' => 'market_manager',
            'item_key' => 'build_type',
            'item_type' => 'number',
            'item_value' => 1,
        ],
    ];

    public function process(callable $callable)
    {
        foreach ($this->config as $configItem) {
            $callable($configItem);
        }
    }

    // plugin install
    public function install()
    {
        // Config::addKeyValues($this->config);
        $this->process(function ($configItem) {
            // add config
            if ($configItem['item_key'] == 'install_datetime') {
                $configItem['item_value'] = date('Y-m-d H:i:s');
            }

            Config::addConfig($configItem);
        });
    }

    /// plugin uninstall
    public function uninstall()
    {
        Config::removeKeyValues($this->config);
        // $this->process(function ($configItem) {
        //     // remove config
        // });
    }
}
