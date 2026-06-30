<?php declare(strict_types=1);

use AppSys\LaravelTeamsLogger\Channel;
use PHPUnit\Framework\TestCase;

final class LoggerTest extends TestCase
{
    private $url = 'https://default38753ae5bbbe495c87217eab004613.14.environment.api.powerplatform.com:443/powerautomate/automations/direct/workflows/d9bd39319300447094fb7e7df917cba8/triggers/manual/paths/invoke?api-version=1&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=g6AT6JTEW-1OzXxh3V6_td-mvCznUYzhn-Qnt4r9VJE';
    public function testCanSendLogMessage(): void
    {
        $config = [
            'url' => $this->url,
        ];

        $channel = new Channel;

        $channel($config)->info('This is a test');
    }

    public function testExceptionMessageIsThrottled(): void
    {
        $config = [
            'url' => $this->url,
            'throttle_exceptions' => true,
            'throttle_ttl' => 5,
        ];

        $channel = new Channel;

        for($i = 0; $i < 3; $i++) {
            $channel($config)->info('This message is being sent three times, but you should see it only twice', ['exception' => new Exception('This is an exception')]);
            sleep(2);
        }
    }
}