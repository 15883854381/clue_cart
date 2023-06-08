<?php

namespace app\controller;

use app\BaseController;

use app\controller\AesUtil;
use think\console\command\make\Model;
use think\facade\Db;
use think\facade\Log;
use think\facade\Queue;
use WpOrg\Requests\Requests as http;
use app\controller\Payment;
use Ramsey\Uuid\Uuid;


class Test
{
    public function index()
    {
        $times = date('Y-m-d', strtotime('2022-05-06 12:25:23'));
        echo date('Y-m-d');
        echo $times;

    }


    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
