<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    // Specify the table name if it's not the default plural form of the model name
    protected $table = 'transactions';

    // Specify the fields that can be mass-assigned
    protected $fillable = [
        'flw_ref', 
        'tx_ref', 
        'amount', 
        'status', 
        'payment_type'
    ];

    // Disable automatic timestamp management by Laravel, since you handle 'created_at' manually
    public $timestamps = false;
    
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
