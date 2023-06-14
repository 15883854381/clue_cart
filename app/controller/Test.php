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

        $request = Request::instance();

        $token = $request->header('token');

//        JWT::$leeway=6000;
        $decoded = JWT::decode($token, new Key(md5('admin'), 'HS256'));
        dump($decoded);
//         JWT::decode($token, new Key(md5('admin'), 'HS256'));

    }


    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
