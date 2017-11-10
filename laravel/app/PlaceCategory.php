<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class PlaceCategory extends Model
{
    protected $table = 'places_categories';

    public $timestamps = false;

    public function place()
    {
        return $this->hasOne('App\Place');
    }

    public function category()
    {
        return $this->hasOne('App\Category');
    }
}