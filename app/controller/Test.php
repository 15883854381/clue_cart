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
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;

class Test
{
    public function index()
    {

        echo base64_encode('hello');
        echo base64_decode('aHR0cDovL2UuMTk5OTA5Lnh5ei8jL2xpc3RfQnVzaW5lc3NfRGV0YWlsP3R5cGU9MSZjbHVlX2lkPTFlZTA1YWEyYmQyNDZmZTBhNzNjMThjMDRkYTMwYjU3');
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}

