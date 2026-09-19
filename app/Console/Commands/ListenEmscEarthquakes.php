<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\EmscEarthquakeProcessor;
use Illuminate\Console\Command;
use Ratchet\Client\Connector;
use React\EventLoop\Loop;
use Throwable;

/**
 * Keeps a persistent WebSocket connection open to EMSC's real-time feed and
 * feeds every message straight into EmscEarthquakeProcessor the instant it
 * arrives (no polling delay). This command never exits on its own — run it
 * as a long-lived background process (see docs/SETUP.md).
 */
class ListenEmscEarthquakes extends Command
{
    protected $signature = 'earthquake:listen';

    protected $description = 'Keep a live WebSocket connection to EMSC and process earthquakes the instant they are published.';

    public function handle(EmscEarthquakeProcessor $processor): int
    {
        $loop = Loop::get();
        $connector = new Connector($loop);
        $url = config('earthquake.emsc.websocket_url');

        $connect = function () use ($connector, $processor, $url, &$connect, $loop): void {
            $connector($url)->then(
                function ($conn) use ($processor, &$connect, $loop): void {
                    $this->info('[earthquake:listen] Connected to EMSC real-time feed.');

                    $conn->on('message', function ($msg) use ($processor): void {
                        try {
                            $payload = json_decode((string) $msg, true, 512, JSON_THROW_ON_ERROR);

                            if (($payload['action'] ?? null) === 'delete') {
                                return;
                            }

                            $properties = $payload['data']['properties'] ?? null;

                            if (is_array($properties)) {
                                $processor->handle($properties);
                            }
                        } catch (Throwable $e) {
                            logger()->error('[earthquake:listen] Failed to process EMSC message: '.$e->getMessage());
                        }
                    });

                    $conn->on('close', function () use (&$connect, $loop): void {
                        logger()->warning('[earthquake:listen] Connection closed. Reconnecting in 5s...');
                        $loop->addTimer(5, $connect);
                    });
                },
                function (Throwable $e) use (&$connect, $loop): void {
                    logger()->error('[earthquake:listen] Connection failed: '.$e->getMessage().'. Retrying in 5s...');
                    $loop->addTimer(5, $connect);
                },
            );
        };

        $connect();
        $loop->run();

        return self::SUCCESS;
    }
}