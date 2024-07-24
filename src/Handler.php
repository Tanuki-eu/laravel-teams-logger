<?php

namespace AppSys\LaravelTeamsLogger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class Handler extends AbstractProcessingHandler
{
    private $url;

    public function __construct($url, $level = \Monolog\Level::Debug)
    {
        parent::__construct($level);

        $this->url = $url;
    }

    protected function write(LogRecord $record): void
    {
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
                                'items' => $this->arrayToColumns($record->context)
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
}
