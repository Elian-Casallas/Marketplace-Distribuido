<?php

namespace App\Jobs;

use App\Models\SyncEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessSyncQueueJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle(): void
    {
        $mainUrl = env('MAIN_API_URL');
        $mainKey = env('MAIN_API_KEY');

        $pending = SyncEvent::whereIn('status', ['pending', 'failed'])->get();

        foreach ($pending as $item) {
            try {
                Log::info("✅ Evento reenviado al main");
                $response = Http::withHeaders([
                    'X-Internal-Key' => $mainKey,
                    'Accept' => 'application/json',
                ])->post(rtrim($mainUrl, '/') . '/api/replicate', $item);

                if ($response->successful()) {
                    Log::info("✅ Synced pending event {$item->_id}");
                    $item->delete(); // o $item->update(['status' => 'synced']);
                } else {
                    Log::warning("⚠️ Sync failed for {$item->_id} - Status: {$response->status()}");
                }
            } catch (\Throwable $e) {
                Log::error("❌ Error syncing {$item->_id}: " . $e->getMessage());
            }
        }
    }
}
