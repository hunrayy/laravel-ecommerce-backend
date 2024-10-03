<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Admin extends Model
{
    use HasFactory;
    protected $table = 'admin'; // This ensures the model uses the 'admin' table

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'user',
        'is_an_admin',
        'countryOfWarehouseLocation',
        'domesticShippingFeeInNaira',
        'internationalShippingFeeInNaira',
    ];

    //override the getIncrementing method
    public $incrementing = false;

    //set the key type to string
    protected $keyType = 'string';
    
    // Automatically create a UUID when inserting
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
