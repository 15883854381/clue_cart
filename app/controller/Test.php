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

        try {
            $sql = "SELECT  CONCAT(user_name,IF(sex=1,'先生','女士')) as user_name,phone_number,payment_time,PhoneBelongingplace,province,city,g.name as CartBrand
                FROM order_list o
                LEFT JOIN clue a ON  a.clue_id = o.clue_id
                LEFT JOIN (SELECT t_city.id,t_province.name as province,t_city.name as city  FROM t_province LEFT JOIN t_city ON t_province.id = t_city.province_id) as c ON c.id = a.cityID
                LEFT JOIN t_car_brand g ON a.CartBrandID = g.id
                WHERE o.flat = 1";
            $res = Db::query($sql);
            $objExcel = new Spreadsheet();
            $objWriter = IOFactory::createWriter($objExcel, 'Xlsx');
            $objActSheet = $objExcel->getActiveSheet(0);
            $objActSheet->setTitle('线索');
            $key = ['姓名', '电话', '下单时间', '号码归属地', '省份', '城市', '意向品牌'];
            array_unshift($res, $key);
            $objActSheet->fromArray($res);
            $media = dirname(dirname(__DIR__)) . '/public/storage/userExcel/' . date('Ymd') . '/';
            mkFolder($media);
            $objWriter->save($media . time() . '.xlsx');
            return success(200, '文件获取成功', null);
        } catch (\Exception $e) {
            return error(304, '文件获取成功', null);
        }
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}

