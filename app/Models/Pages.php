<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pages extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'page',
        'firstSection',
        'secondSection',
        'thirdSection',
        'fourthSection',
        'fifthSection',
        'sixthSection',
        'seventhSection',
        'eighthSection',
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
