<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
