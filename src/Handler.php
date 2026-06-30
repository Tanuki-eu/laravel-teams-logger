<?php

namespace AppSys\LaravelTeamsLogger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class Handler extends AbstractProcessingHandler
{
    private static array $throttleCache = [];

    public function __construct(
        private string   $url,
        int|Level|string $level = Level::Debug,
        private bool     $throttleExceptions = false,
        private int      $throttleTtl = 60,
    )
    {
        parent::__construct($level);
    }

    protected function write(LogRecord $record): void
    {
        if ($this->shouldThrottle($record)) {
            return;
        }

        $json = json_encode([
            'type' => 'message',
            'attachments' => [
                [
                    'contentType' => 'application/vnd.microsoft.card.adaptive',
                    'contentUrl' => null,
                    'content' => [
                        '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                        'type' => 'AdaptiveCard',
                        'version' => '1.2',
                        'msteams' => [
                            'width' => 'Full'
                        ],
                        'body' => [
                            [
                                'type' => 'TextBlock',
                                'text' => $record->level->getName(),
                                'color' => match ($record->level) {
                                    Level::Debug => 'light',
                                    Level::Info => 'default',
                                    Level::Notice => 'accent',
                                    Level::Warning => 'warning',
                                    Level::Error, Level::Alert, Level::Critical, Level::Emergency => 'attention',
                                },
                                'weight' => 'bolder'
                            ],
                            [
                                'type' => 'TextBlock',
                                'text' => $record->message,
                                'wrap' => true
                            ],
                            [
                                'type' => 'Container',
                                'items' => $this->arrayToColumns($record->formatted['context'])
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json),
        ]);

        curl_exec($ch);
    }

    protected function arrayToColumns(array $array): array
    {
        $output = [];
        foreach ($array as $key => $value) {
            if ($key === 'trace') {
                continue;
            }
            $output[] = [
                'type' => 'ColumnSet',
                'columns' => [
                    [
                        'type' => 'Column',
                        'width' => 'auto',
                        'items' => [
                            [
                                'type' => 'TextBlock',
                                'text' => "- $key:",
                                'wrap' => true
                            ]
                        ]
                    ],
                    [
                        'type' => 'Column',
                        'width' => 'auto',
                        'items' => is_array($value)
                            ? $this->arrayToColumns($value)
                            : [
                                [
                                    'type' => 'TextBlock',
                                    'text' => $value ?? 'null',
                                    'wrap' => true
                                ]
                            ]
                    ]
                ]
            ];
        }

        return $output;
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new NormalizerFormatter();
    }

    private function shouldThrottle(LogRecord $record)
    {
        if (!$this->throttleExceptions) {
            return false;
        }

        $exception = $record->context['exception'] ?? null;

        if (!$exception instanceof \Throwable) {
            return false;
        }

        $key = md5($exception::class . '|' . $exception->getMessage());
        $now = time();

        if (
            isset(self::$throttleCache[$key]) &&
            self::$throttleCache[$key] > $now
        ) {
            return true;
        }

        self::$throttleCache[$key] = $now + $this->throttleTtl;

        return false;
    }
}
