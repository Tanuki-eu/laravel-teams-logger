<?php

namespace AppSys\LaravelTeamsLogger;

use Monolog\Level;
use Monolog\Logger;

class Channel
{
    public function __invoke(array $config)
    {
        return new Logger('teams-logger', [
            new Handler(
                url: $config['url'],
                level: $config['level'] ?? Level::Debug,
                throttleExceptions: $config['throttle_exceptions'] ?? false,
                throttleTtl: $config['throttle_ttl'] ?? 60,
            ),
        ]);
    }
}
