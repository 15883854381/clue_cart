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
       echo unlink(root_path() . 'public/storage/' . 'process/20230701\\27656a735671c893a3d8635824ca2961.png');

        echo root_path() . 'public/storage/' . 'process/20230701\\27656a735671c893a3d8635824ca2961.png';

    }


    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
