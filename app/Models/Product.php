<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'productName',
        'productImage',
        'subImage1',
        'subImage2',
        'subImage3',
        'productPriceInNaira12Inches',
        'productPriceInNaira14Inches',
        'productPriceInNaira16Inches',
        'productPriceInNaira18Inches',
        'productPriceInNaira20Inches',
        'productPriceInNaira22Inches',
        'productPriceInNaira24Inches',
        'productPriceInNaira26Inches',
        'productPriceInNaira28Inches',

    ];

    // Disable the auto-incrementing feature
    public $incrementing = false;

    // Set the key type to string
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Automatically set the id to a new UUID when creating
            $model->id = (string) Str::uuid();
        });
    }
}
    