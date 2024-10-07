<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_id', 'user_id', 'firstname', 'lastname', 'email', 'country', 'state', 
        'address', 'city', 'postalCode', 'phoneNumber', 'totalPrice', 
        'currency', 'products', 'status'
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
