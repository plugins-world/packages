<?php

namespace Plugins\LaravelDoc\Console\Commands;

use Illuminate\Console\Command;

class ApiDocCleanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-doc:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清理生成的 api 文档';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        shell_exec('rm -rf '.storage_path('app/yapi'));
    }
}
