<?php

namespace Tests\Yapi;

use Tests\TestCase;
use Cblink\YApiDoc\YapiDTO;

class YapiTest extends TestCase
{
    use \Plugins\LaravelDoc\Traits\YapiTrait;

    /**
     * 上传yapi文件
     */
    public function test_upload()
    {
        dispatch_sync(new \Plugins\LaravelDoc\Jobs\YapiJobs());
        dispatch_sync(new \Plugins\LaravelDoc\Jobs\ApifoxJobs());

        $this->assertTrue(true);
    }

    public function test_api_request()
    {
        $response = $this->getJsonWithQuery('/api');

        // 断言接口响应数据格式
        $this->assertSuccess($response, []);

        // 生成 yapi 文档 与 openapi 2.0 文档
        $this->yapi($response, new YapiDTO([
            // 可以同时生成到多个 yapi 项目
            'project' => ['default'],
            // api 名称
            'name' => '验证接口是否可以请求',
            // api 分类
            'category' => '系统',
            'params' => [],
            'desc' => '',
            'request' => [
                // 这里是字段含义
                'trans' => [],
                // 这里是非必填字段
                'except' => [],
            ],
            'response' => [
                // 这里是字段含义
                'trans' => [],
                // 这里是非必填字段
                'except' => [],
            ],
        ]));
    }
}
