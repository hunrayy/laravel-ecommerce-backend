<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Shipping extends Model
{
    use HasFactory;
    protected $table = 'shipping'; // This ensures the model uses the 'shipping' table
    
    protected $fillable = [
        'countryOfWarehouseLocation',
        'domesticShippingFee',  // Updated field name
        'internationalShippingFee',  // Updated field name
        'numberOfDaysForDomesticDelivery',
        'numberOfDaysForInternationalDelivery',
    ];
    

    //disable the auto incrementing feature
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
