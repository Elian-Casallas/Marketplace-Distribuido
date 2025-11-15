<?php

namespace App\Models;

use Mongodb\Laravel\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PendingReplication extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'pending_replications';

    protected $fillable = [
        'node',          // A qué nodo iba el evento
        'payload',       // Contenido del evento (JSON)
        'status',        // pending | sent | failed
        'last_error',    // Texto con el último error si falló
        'attempts',      // Número de intentos realizados
    ];
}
