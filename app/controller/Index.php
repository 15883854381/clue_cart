<?php

namespace app\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {


    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
