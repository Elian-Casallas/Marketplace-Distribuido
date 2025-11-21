<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\PendingReplication;
use App\Models\SyncLog;

class ProcessPendingReplicationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $pending = PendingReplication::whereIn('status', ['pending', 'failed'])->get();
        foreach ($pending as $item) {
            try {
                $nodeUrl = match ($item->node) {
                    'MarketplaceClothes' => env('CLOTHES_API_URL'),
                    'MarketplaceElectronics' => env('ELECTRONICS_API_URL'),
                    'MarketplaceHome' => env('HOME_API_URL'),
                    default => null,
                };

                $nodeKey = match ($item->node) {
                    'MarketplaceClothes' => env('CLOTHES_API_KEY'),
                    'MarketplaceElectronics' => env('ELECTRONICS_API_KEY'),
                    'MarketplaceHome' => env('HOME_API_KEY'),
                    default => null,
                };

                if (!$nodeUrl || !$nodeKey) {
                    throw new \Exception("No se encontró URL para el nodo {$item->node}");
                }

                Log::info("✅ Evento reenviado a {$nodeUrl}");

                $resp = Http::withHeaders([
                    'X-Internal-Key' => $nodeKey,
                    'Accept' => 'application/json',
                ])->post(rtrim($nodeUrl, '/') . "/api/replicate", $item->payload);

                if ($resp->successful()) {
                    $item->delete();
                    SyncLog::create([
                        'direction' => 'main->node',
                        'target' => $item->node,
                        'event_id' => $item->_id,
                        'status' => 'success',
                        'message' => 'Replicación enviada exitosamente',
                        'timestamp' => now(),
                    ]);
                    Log::info("✅ Evento reenviado a {$item->node}");
                } else {
                    throw new \Exception("HTTP error: " . $resp->status());
                }
            } catch (\Throwable $e) {
                $item->increment('attempts');
                $item->update([
                    'status' => 'failed',
                    'last_error' => $e->getMessage(),
                ]);

                SyncLog::create([
                    'direction' => 'main->node',
                    'target' => $item->node,
                    'event_id' => $item->_id,
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                    'timestamp' => now(),
                ]);

                Log::error("❌ Fallo reenviando a {$item->node}: {$e->getMessage()}");
            }
        }
    }

}
