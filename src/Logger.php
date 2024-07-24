<?php

namespace AppSys\LaravelTeamsLogger;

use Monolog\Logger as MonologLogger;

class Logger extends MonologLogger
{
    public function __construct($level, $url)
    {
        parent::__construct('teams-logger', [new Handler($url, $level)]);
    }
}
