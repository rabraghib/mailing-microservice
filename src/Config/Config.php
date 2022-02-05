<?php

namespace App\Config;

class Config implements ConfigInterface
{
    private array $configs;

    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    public function get(string $key = '') : mixed
    {
        return (empty($key)) ? $this->configs : $this->configs[$key];
    }
}
