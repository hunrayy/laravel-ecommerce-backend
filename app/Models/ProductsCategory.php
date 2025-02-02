<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Define the table name to avoid Laravel's pluralization
    protected $table = 'products_category'; 

    // Define the relationship to products
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
