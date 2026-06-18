<?php

// app/Models/Country.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    protected $fillable = ['ID', 'name', 'short_name', 'region'];

    public $timestamps = false;

    protected $primaryKey = 'ID';
    public $incrementing = false;
}

