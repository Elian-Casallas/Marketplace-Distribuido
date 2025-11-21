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
use App\Models\SyncEvent;

class ReplicateProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $event;
    public $tries = 5;             // Intentos antes de marcar como fallido
    public $backoff = [5, 30, 60]; // Segundos entre reintentos

    /**
     * Create a new job instance.
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $mainUrl = env('MAIN_API_URL');
        $mainKey = env('MAIN_API_KEY');
        if (!$mainUrl || !$mainKey) {
            Log::error('Replication config missing: MAIN_API_URL or MAIN_API_KEY');
            $this->saveToSyncQueue('config_missing');
            return;
        }

        try {
            $resp = Http::withHeaders([
                'X-Internal-Key' => $mainKey,
                'Accept' => 'application/json',
            ])->post(rtrim($mainUrl, '/')."/api/replicate", $this->event);

            if ($resp->successful()) {
                Log::info("✅ Replicated event {$this->event['event_id']} to main-api");
                return;
            }

            // Forzar reintento automático por el worker Redis
            throw new Exception('Replication HTTP error: ' . $resp->status() . ' - ' . $resp->body());
        } catch (\Throwable $e) {
            // Distinguimos connectivity/timeouts vs other exceptions
            Log::error("Replication attempt failed: " . $e->getMessage(), [
                'event_id' => $this->event['event_id'] ?? null,
                'exception' => $e->__toString(),
            ]);

            // Si es un problema de conexión o timeout, lo guardamos en la cola local
            $this->saveToSyncQueue($e->getMessage());

            // Re-lanzar para que el sistema de jobs aplique retries si quieres
            // throw $e;
        }
    }

    protected function saveToSyncQueue($errorMessage = null)
    {
        try {
            SyncEvent::create([
                'event_id' => $this->event['event_id'] ?? \Illuminate\Support\Str::uuid()->toString(),
                'action' => $this->event['action'] ?? 'update',
                'product' => $this->event['product'] ?? null,
                'status' => 'pending',
                'tries' => 0,
                'last_error' => $errorMessage,
            ]);

            Log::info("Saved event to local sync_queue", ['event_id' => $this->event['event_id'] ?? null]);

        } catch (\Throwable $e) {
            Log::error("Failed to write to sync_queue: " . $e->getMessage());
        }
    }

    /**
     * Acción si fallan todos los intentos.
     */
    public function failed(Exception $exception)
    {
        Log::error("❌ ReplicateProductJob failed for {$this->event['event_id']}: " . $exception->getMessage());
    }
}
