<?php

namespace App\Models;

use Mongodb\Laravel\Eloquent\Model;
use Illuminate\Support\Arr;

class SyncEvent extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sync_queue'; // nombre de la colección local
    protected $guarded = [];
    public $timestamps = true; // created_at / updated_at

    // Status values: pending, sending, failed, synced
}
