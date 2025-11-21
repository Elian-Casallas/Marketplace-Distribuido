<?php
namespace App\Models;

use Mongodb\Laravel\Eloquent\Model;
use Illuminate\Support\Arr;

class Product extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category',
        'link',
        'seller_id',
        'attributes', // campo flexible (array/json)
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'seller_id' => 'string',
    ];

    // helper to get attribute value safely
    public function getAttributeValue($key, $default = null)
    {
        return Arr::get($this->attributes['attributes'] ?? [], $key, $default);
    }
}
