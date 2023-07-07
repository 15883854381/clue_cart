<?php

namespace app\controller;

use app\BaseController;

use app\controller\AesUtil;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\console\command\make\Model;
use think\facade\Db;
use think\facade\Env;
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
        $res = Db::table('order_temporary')->where('state',1)->select()->toArray();
        if ($res) {
            return 1;
        } else {
            return 2;
        }


    }


    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
