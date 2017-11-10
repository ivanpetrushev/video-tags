<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Place extends Model
{
    protected $table = 'places';

    public function links()
    {
        return $this->hasMany('App\PlaceLink', 'place_id');
    }

    public function categories()
    {
        return $this->hasMany('App\PlaceCategory', 'place_id');
    }
}