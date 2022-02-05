<?php

namespace App\Config;

interface ConfigInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key = '') : mixed;
}
