<?php

namespace App\Models;

use Mongodb\Laravel\Eloquent\Model;
use Illuminate\Support\Arr;

class ProcessedEvent extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'processed_events';
    protected $fillable = ['event_id','source','action','payload','processed_at'];
    public $timestamps = false;
}
