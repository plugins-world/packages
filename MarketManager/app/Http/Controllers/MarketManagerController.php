<?php

namespace Plugins\MarketManager\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Fresns\MarketManager\Models\Plugin;

class MarketManagerController extends Controller
{
    public function index()
    {
        $plugins = Plugin::all();
        
        return view('MarketManager::index', [
            'plugins' => $plugins,
        ]);
    }
}
