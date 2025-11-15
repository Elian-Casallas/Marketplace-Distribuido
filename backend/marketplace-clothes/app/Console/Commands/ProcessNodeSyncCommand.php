<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessSyncQueueJob;

class ProcessNodeSyncCommand extends Command
{
    protected $signature = 'sync:process-nodes';
    protected $description = 'Procesa los eventos pendientes de replicación hacia el main-api';

    public function handle()
    {
        ProcessSyncQueueJob::dispatch();
        $this->info('🚀 Job ProcessSyncQueueJob despachado correctamente.');
    }
}
