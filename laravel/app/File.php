<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class File extends Model
{
    protected $table = 'files';
    public $timestamps = false;

    public function directory()
    {
        return $this->belongsTo('App\Directory');
    }

    public function tags()
    {
        return $this->hasMany('App\FileTag');
    }
}