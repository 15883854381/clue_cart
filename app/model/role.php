<?php

namespace app\model;

use think\Model;

class role extends Model
{

    function admin_role()
    {
        return $this->hasOne('admin','authority');
    }

}