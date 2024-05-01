<?php

namespace Plugins\Translate\Kernel\Traits;

trait InteractWithConfig
{
    protected $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }
}
