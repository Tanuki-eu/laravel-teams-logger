<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class LoggerTest extends TestCase
{
    public function testCanSendLogMessage(): void
    {
        $config = [
            'url' => 'https://prod-148.westeurope.logic.azure.com:443/workflows/d9bd39319300447094fb7e7df917cba8/triggers/manual/paths/invoke?api-version=2016-06-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=CzdSWuOqG7f8KMR-vvBlBMr1SOwybX-ZAZPkmcV2ubA'
        ];

        $channel = new \AppSys\LaravelTeamsLogger\Channel;
        $this->assertInstanceOf(\AppSys\LaravelTeamsLogger\Logger::class, $channel($config));

        $channel($config)->info('Dit is een test');
    }
}