<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\EmscEarthquakeProcessor;
use Illuminate\Console\Command;
use Ratchet\Client\Connector;
use Ratchet\RFC6455\Messaging\Frame;
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

        // A connection can go quietly dead without ever firing a 'close'
        // event — e.g. a cloud network's NAT/proxy layer silently dropping
        // an idle long-lived connection. The reconnect-on-close handler
        // below can't see that, so a watchdog actively pings the server
        // every $pingIntervalSeconds and forces a reconnect if nothing
        // (message or pong) has been heard for $staleAfterSeconds.
        $pingIntervalSeconds = 60;
        $staleAfterSeconds = 180;

        $connect = function () use ($connector, $processor, $url, &$connect, $loop, $pingIntervalSeconds, $staleAfterSeconds): void {
            $connector($url)->then(
                function ($conn) use ($processor, &$connect, $loop, $pingIntervalSeconds, $staleAfterSeconds): void {
                    $this->info('[earthquake:listen] Connected to EMSC real-time feed.');

                    $lastActivity = time();

                    $conn->on('message', function ($msg) use ($processor, &$lastActivity): void {
                        $lastActivity = time();
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

                    $conn->on('pong', function () use (&$lastActivity): void {
                        $lastActivity = time();
                    });

                    $watchdogTimer = $loop->addPeriodicTimer(
                        $pingIntervalSeconds,
                        function () use ($conn, &$lastActivity, $staleAfterSeconds): void {
                            try {
                                if ((time() - $lastActivity) > $staleAfterSeconds) {
                                    logger()->warning('[earthquake:listen] Connection looked stale (no activity for over '.$staleAfterSeconds.'s). Forcing reconnect.');
                                    // Triggers the existing 'close' handler below,
                                    // which already knows how to reconnect — this
                                    // never adds a second, competing reconnect path.
                                    $conn->close();

                                    return;
                                }

                                $conn->send(new Frame(null, true, Frame::OP_PING));
                            } catch (Throwable $e) {
                                logger()->warning('[earthquake:listen] Watchdog check failed: '.$e->getMessage());
                            }
                        }
                    );

                    $conn->on('close', function () use (&$connect, $loop, $watchdogTimer): void {
                        $loop->cancelTimer($watchdogTimer);
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