<?php

namespace Plugins\LaravelDoc\Console\Commands;

use Illuminate\Console\Command;

class ApiDocUploadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-doc:upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '上传生成的 api 文档';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch_sync(new \Plugins\LaravelDoc\Jobs\YapiJobs());
        dispatch_sync(new \Plugins\LaravelDoc\Jobs\ApifoxJobs());
    }
}
