<?php

namespace app\controller;

use app\BaseController;

use app\controller\AesUtil;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\console\command\make\Model;
use think\facade\Db;
use think\facade\Log;
use think\facade\Queue;
use think\facade\Request;
use WpOrg\Requests\Requests as http;
use app\controller\Payment;
use Ramsey\Uuid\Uuid;


class Test
{
    public function index()
    {

        echo date("Y-m-d", strtotime("-7 day"));
        echo date("Y-m-d");

    }


    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
