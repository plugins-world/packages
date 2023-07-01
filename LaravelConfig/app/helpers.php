<?php

if (! function_exists('is_tenant_mode')) {
    function is_tenant_mode(): mixed
    {
        return function_exists('tenancy');
    }
}

if (! function_exists('central')) {
    function central(callable $callable): mixed
    {
        if (! is_tenant_mode()) {
            $tenant = null;

            return $callable($tenant);
        }

        return tenancy()->central(function ($tenant) use ($callable) {
            return $callable($tenant);
        });
    }
}
