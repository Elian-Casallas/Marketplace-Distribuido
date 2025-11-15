<?php
namespace App\Models;

use Mongodb\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;

class Product extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category',
        'attributes', // campo flexible (array/json)
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
    ];

    // helper to get attribute value safely
    public function getAttributeValue($key, $default = null)
    {
        return Arr::get($this->attributes['attributes'] ?? [], $key, $default);
    }
}
