<?php

namespace App\Models;

use Mongodb\Laravel\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SyncLog extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'sync_logs';

    protected $fillable = [
        'direction',     // 'main->node' o 'node->main'
        'target',        // nodo o main
        'event_id',      // ID único del evento
        'status',        // success | failed
        'message',       // detalle o error
        'timestamp',
    ];
}
