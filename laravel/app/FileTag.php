<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class FileTag extends Model
{
    protected $table = 'files_tags';

    public $timestamps = false;

    public function file()
    {
        return $this->belongsTo('App\File');
    }

    public function tag()
    {
        return $this->belongsTo('App\Tag');
    }
}