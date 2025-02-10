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
        'category_id',
        'subImage1',
        'subImage2',
        'subImage3',
        'productPrice12Inches',
        'productPrice14Inches',
        'productPrice16Inches',
        'productPrice18Inches',
        'productPrice20Inches',
        'productPrice22Inches',
        'productPrice24Inches',
        'productPrice26Inches',
        'productPrice28Inches',
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

    // Define the relationship to ProductCategory
    public function category()
    {
        return $this->belongsTo(ProductsCategory::class);
    }
}
    