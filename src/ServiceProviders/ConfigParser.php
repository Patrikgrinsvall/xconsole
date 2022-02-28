<?php

namespace PatrikGrinsvall\XConsole\ServiceProviders;

class ConfigParser
{
    public function load()
    {
        $config = __DIR__ . '/../../console.srv.yml';
        $config = yaml_parse_file($config);

    }
}