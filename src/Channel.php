<?php

namespace AppSys\LaravelTeamsLogger;

use Monolog\Level;

class Channel
{
    public function __invoke(array $config)
    {
        return new Logger($config['level'] ?? Level::Debug, $config['url']);
    }
}
