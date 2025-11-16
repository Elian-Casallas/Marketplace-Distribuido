<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessSyncQueueJob;
use Illuminate\Support\Facades\Log;

class ProcessNodeSyncCommand extends Command
{
    protected $signature = 'sync:process-node_electronics';
    protected $description = 'Procesa los eventos pendientes de replicación hacia el main-api';

    public function handle()
    {
        Log::info('🔧 [ProcessNodeSyncCommand] Iniciando ejecución del comando...');
        ProcessSyncQueueJob::dispatch();
        $this->info('🚀 Job ProcessSyncQueueJob despachado correctamente.');
        Log::info('✔️ [ProcessNodeSyncCommand] Comando ejecutado con éxito.');
    }
}
