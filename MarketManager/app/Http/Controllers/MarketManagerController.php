<?php

namespace Plugins\MarketManager\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Fresns\MarketManager\Models\Plugin;
use MouYong\LaravelConfig\Models\Config;

class MarketManagerController extends Controller
{
    public function index()
    {
        $configs = Config::getValueByKeys([
            'market_server_host',
            'system_url',
            'install_datetime',
            'build_type',
        ]);
        
        $plugins = Plugin::all();
        
        return view('MarketManager::index', [
            'configs' => $configs,
            'plugins' => $plugins,
        ]);
    }
}
