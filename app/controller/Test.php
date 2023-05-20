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


//        $user = new \app\model\User();
//        $date = date('Y年m月d');
//        $userdata = $user->field('openid,nickname')->where([['id', '<>', '51']])->select();
//        $clue = new \app\model\Clue();
//        $count = $clue->where('flag', 1)->count();
//        $ulitsThree = new UlitsThree();
//        foreach ($userdata as $item) {
//            $ulitsThree->sendWeiXinTempleat_notConter($item, $count, $date);
//        }
    }


    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
