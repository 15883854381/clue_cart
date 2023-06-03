<?php

namespace app\model;

use think\Model;

class admin extends Model
{
    function role(){

        return $this->belongsTo('role','authority');
    }
}