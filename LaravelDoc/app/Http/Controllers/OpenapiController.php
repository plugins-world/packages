<?php

namespace Plugins\LaravelDoc\Http\Controllers;

use Illuminate\Routing\Controller;

class OpenapiController extends Controller
{
    public function show()
    {
        $file = config('yapi.openapi.path', public_path('openapi.json'));

        if (!file_exists($file)) {
            return 'openapi.json file does not exists';
        }

        return file_get_contents(config('yapi.openapi.path', public_path('openapi.json')));
    }

    public function example()
    {
        // 空接口，不做任何实际用途
        if (\request()->wantsJson()) {
            return \response()->json();
        }

        return \response('示例接口无响应内容');
    }
}
