<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class LoggerTest extends TestCase
{
    public function testCanSendLogMessage(): void
    {
        $config = [
            'url' => 'https://default38753ae5bbbe495c87217eab004613.14.environment.api.powerplatform.com:443/powerautomate/automations/direct/workflows/d9bd39319300447094fb7e7df917cba8/triggers/manual/paths/invoke?api-version=1&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=g6AT6JTEW-1OzXxh3V6_td-mvCznUYzhn-Qnt4r9VJE'
        ];

        $channel = new \AppSys\LaravelTeamsLogger\Channel;
        $this->assertInstanceOf(\AppSys\LaravelTeamsLogger\Logger::class, $channel($config));

        $channel($config)->info('Dit is een test');
    }
}