<?php

namespace Plugins\MarketManager\Utilities;

use Fresns\PluginManager\Plugin;

class PluginUtility
{
    public static function qualifyUrl(Plugin $plugin, string $key)
    {
        return StrUtility::qualifyUrl($plugin[$key], $plugin['plugin_host']);
    }
}