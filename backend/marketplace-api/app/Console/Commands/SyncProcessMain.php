<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessPendingReplicationsJob;

class SyncProcessMain extends Command
{
    protected $signature = 'sync:process-main';
    protected $description = 'Procesa las replicaciones pendientes del main-api hacia los nodos';

    public function handle()
    {
        ProcessPendingReplicationsJob::dispatch();
        $this->info('🟢 Job ProcessPendingReplicationsJob despachado correctamente.');
    }
}
