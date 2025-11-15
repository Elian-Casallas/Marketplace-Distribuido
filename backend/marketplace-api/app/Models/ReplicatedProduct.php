<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ReplicatedProduct extends Model
{
    protected $connection = 'mongodb';
    protected $guarded = [];

    public static function forCategory(string $suffix)
    {
        $instance = new static;
        $instance->setTable("replicated_products_{$suffix}");
        return $instance;
    }
}
