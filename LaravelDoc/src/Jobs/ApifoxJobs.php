<?php

namespace Plugins\LaravelDoc\Jobs;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ApifoxJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config ?: config('yapi', []);
    }

    public function handle()
    {
        if (!Arr::get($this->config, 'apifox.enable', false)) {
            return;
        }

        $result = $this->upload();

        $this->line(sprintf(
            "Apifox 同步数据成功 \n\t新增 %d\n\t修改 %d\n\t忽略 %d\n\t错误 %d\n\t",
            Arr::get($result, 'data.apiCollection.item.createCount'),
            Arr::get($result, 'data.apiCollection.item.updateCount'),
            Arr::get($result, 'data.apiCollection.item.ignoreCount'),
            Arr::get($result, 'data.apiCollection.item.errorCount')
        ));

        $url = 'https://www.apifox.cn/web/project/' . Arr::get($this->config, 'apifox.project_id', 0);
        $this->line("接口同步完成，访问地址：" . $url);
    }

    public function getHttpClient()
    {
        $client = new Client([
            'base_uri' => 'https://api.apifox.cn',
            'timeout' => 5, // Request 5s timeout
            'http_errors' => false,
            'headers' => $this->getHeaders(),
        ]);

        return $client;
    }

    public function getHeaders()
    {
        $token = Arr::get($this->config, 'apifox.user_token', null);

        $headers = [
            'X-Apifox-Version' => Arr::get($this->config, 'apifox.api_version', '2022-11-16'),
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        if (!$token) {
            unset($headers['Authorization']);
        }

        return $headers;
    }

    public function upload()
    {
        $projectId = Arr::get($this->config, 'apifox.project_id');
        $content = file_get_contents(Arr::get($this->config, 'openapi.path', public_path('openapi.json')));

        $resp = $this->getHttpClient()->post("/api/v1/projects/{$projectId}/import-data", [
            'form_params' => [
                'importFormat' => 'openapi',
                'data' => $content ?? '',
                'apiOverwriteMode' => $option['apiOverwriteMode'] ?? 'methodAndPath',
                'schemaOverwriteMode' => $option['schemaOverwriteMode'] ?? 'merge',
            ],
        ]);

        return json_decode($resp->getBody()->getContents(), true) ?? [];
    }

    public function line($message)
    {
        dump($message);
    }
}
