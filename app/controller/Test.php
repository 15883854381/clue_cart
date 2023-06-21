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

        $str = "<div class=\"layui-btn-group tool-i\">1<a onclick=\"tool_record('https%3A%2F%2Fs28001701.qh.zcallr.cn%2Fmonitor%2F2023%2F06%2F20%2Fq-6001-unknown-20230620-145244-1687243933.19632.wav')\" class=\"layui-btn layui-btn-primary layui-btn-xs fa fa-play-circle\"  title=\"播放录音\"></a></div>";

        $findnum = stripos($str, 'wav');
        echo urldecode(substr($str, 62, -103 ));

    }


    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
