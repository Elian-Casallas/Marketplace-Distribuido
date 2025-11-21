<?php

namespace App\Models;

use Mongodb\Laravel\Eloquent\Model;
use Illuminate\Support\Arr;

class UserMarketplace extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'usersMarketplace';

    protected $fillable = [
        'name',
        'email',
        'password',
        'identificacion',
        'isSeller',
        'telefono',
        'productosVenta', // array con: node + product_id
        'domicilio',
    ];

    protected $casts = [
        'isSeller' => 'boolean',
        'identificacion' => 'string',
    ];
}
