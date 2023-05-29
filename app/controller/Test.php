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
//        $fruits = array('apple', 'orange', 'banana', 'grape');
//        if (in_array('orange', $fruits)) {
//            echo '存在';
//        } else {
//            echo '不存在';
//        }
        $hour = date('H');
        $fruits = [9, 10,11,12, 14, 16, 18, 19];
        if (!in_array($hour, $fruits)) {
            echo  '不存在';
        }else{
            echo '存在';
        }


    }


    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
