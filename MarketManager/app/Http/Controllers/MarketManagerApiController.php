<?php

namespace Plugins\MarketManager\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use ZhenMu\Support\Traits\ResponseTrait;

class MarketManagerApiController extends Controller
{
    use ResponseTrait;

    public function install()
    {
        \request()->validate([
            'installType' => 'nullable', // plugin, theme
            'installMethod' => 'required|in:inputUnikey,inputDirectory,inputZipball',

            'inputUnikey' => 'required_if:installMethod,inputUnikey',
            'inputDirectory' => 'required_if:installMethod,inputDirectory',
            'inputZipball' => 'required_if:installMethod,inputZipball',
        ]);

        $installType = \request('installType', 'plugin');
        $installMethod = \request('installMethod');
        $installValue = \request($installMethod);

        switch ($installMethod) {
            // unikey
            case 'inputUnikey':
                // market-manager
                $exitCode = Artisan::call('market:require', [
                    'unikey' => $installValue,
                ]);
                $output = Artisan::output();
            break;

            // directory
            case 'inputDirectory':
                $pluginDirectory = $installValue;

                if (strpos($pluginDirectory, '/') == false) {
                    $pluginDirectory = "extensions/{$installType}s/{$pluginDirectory}";
                }

                if (str_starts_with($pluginDirectory, '/')) {
                    $pluginDirectory = realpath($pluginDirectory);
                } else {
                    $pluginDirectory = realpath(base_path($pluginDirectory));
                }

                // plugin-manager or theme-manager
                $exitCode = Artisan::call("{$installType}:install", [
                    'path' => $pluginDirectory,
                ]);
                $output = Artisan::output();
            break;

            // inputZipball
            case 'inputZipball':
                $pluginZipball = null;
                $file = $installValue;

                if ($file && $file->isValid()) {
                    $dir = storage_path('extensions');
                    $filename = $file->hashName();
                    $file->move($dir, $filename);

                    $pluginZipball = "$dir/$filename";
                }

                if (empty($pluginZipball)) {
                    return $this->fail('插件安装失败，请选择插件压缩包');
                }

                // plugin-manager or theme-manager
                $exitCode = Artisan::call("{$installType}:install", [
                    'path' => $pluginZipball,
                ]);
                $output = Artisan::output();
            break;
        }

        if ($exitCode != 0) {
            if ($output == '') {
                $output = "请查看安装日志 storage/logs/laravel.log";
            }

            return \response($output."\n 安装失败");
        }

        return \response($output."\n 安装成功");
    }

    public function update()
    {
        \request()->validate([
            'plugin' => 'required|string',
            'is_enable' => 'required|boolean'
        ]);

        // $plugin = Plugin::findById(\request()->post('id'));

        // if (!$plugin) {
        //     return $this->fail('插件不存在或已删除', 200);
        // }
        // $plugin->is_enable = \request()->post('is_enable');
        // $plugin->save();

        $unikey = \request('plugin');
        if (\request()->get('is_enable') != 0) {
            $exitCode = Artisan::call('market:activate', ['unikey' => $unikey]);
        } else {
            $exitCode = Artisan::call('market:deactivate', ['unikey' => $unikey]);
        }

        if ($exitCode !== 0) {
            return $this->fail(Artisan::output());
        }

        return $this->success();
    }

    public function uninstall()
    {
        \request()->validate([
            'plugin' => 'required|string',
            'clearData' => 'nullable|bool',
        ]);

        // Plugin::deletePlugin(\request()->post('id'));

        $unikey = \request('plugin');
        if (\request()->get('clearData') == 1) {
            $exitCode = Artisan::call('market:remove-plugin', ['unikey' => $unikey, '--cleardata' => true]);
        } else {
            $exitCode = Artisan::call('market:remove-plugin', ['unikey' => $unikey, '--cleardata' => false]);
        }

        $message = '卸载成功';
        if ($exitCode != 0) {
            $message = Artisan::output()."\n卸载失败";
        }

        return \response($message);
    }

}
