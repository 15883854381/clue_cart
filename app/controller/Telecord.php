<?php

namespace app\controller;

use app\BaseController;
use think\cache\driver\Redis;
use think\facade\Config;
use think\facade\Filesystem;
use think\Log;
use WpOrg\Requests\Requests as http;

class Telecord extends BaseController
{

    // 登录
    public function login()
    {

        $redis = new Redis(Config::get('cache.stores.redis'));
        $phpSessionID = $redis->get('sessionID');
        if (!$phpSessionID) {
            $data = [
                "type" => 'password',
                'user' => '9000',
                'password' => 'zz888',
                'smscode' => '',
            ];

            $url = "https://s28001701.qh.zcallr.cn/groupui/?action=login_action";
            $options = array('verify' => false);
            $response = http::post($url, [], $data, $options);
            $findnum = stripos($response->raw, 'PHPSESSID');
            $phpSessionID = substr($response->raw, $findnum, 36);
            $redis->set('sessionID', $phpSessionID, 1200);
        }

        return $phpSessionID;
    }


    // 获取cookie
    private function getCookie()
    {
        $redis = new Redis(Config::get('cache.stores.redis'));
        $phpSessionID = $redis->get('sessionID');
        if (!$phpSessionID) {
            return $this->login();
        }
        return $phpSessionID;
    }


    // 获取 通话记录
    public function getData($taskid)
    {
        $url = 'https://s28001701.qh.zcallr.cn/groupui/module/callrecord/?action=listdata';
        $header = [
            "Cookie" => $this->getCookie(),
            "Host" => "s28001701.qh.zcallr.cn",
            "Origin" => "https://s28001701.qh.zcallr.cn",
            "Referer" => "https://s28001701.qh.zcallr.cn/groupui/module/callrecord/",
        ];
        $options = array('verify' => false);
        $data = [
            "page" => "1",
            "limit" => "200",
            "form" => "action=%7Baction%7D&taskid=$taskid&id=&phoneid=&answered=1&calldate=&exten=&phone=&waitsec=",

        ];
        $response = http::post($url, $header, $data, $options);
        return json_decode($response->body, true);
    }


    // 获取任务记录的 ID
    public function task()
    {
        $url = "https://s28001701.qh.zcallr.cn/groupui/module/task/?action=listdata";
        $options = array('verify' => false);
        $data = [
            "page" => "1",
            "limit" => "200",
            "form" => "action=%7Baction%7D&uniid=&name=&state=&creattime=2023-06-10%20-%202023-06-25",
        ];
        $header = [
            "Cookie" => $this->getCookie(),
            "Host" => "s28001701.qh.zcallr.cn",
            "Origin" => "https://s28001701.qh.zcallr.cn",
            "Referer" => "https://s28001701.qh.zcallr.cn/groupui/module/callrecord/",
        ];

        $response = http::post($url, $header, $data, $options);
        $data = json_decode($response->body, true);
        $arrData = [];
        foreach ($data['data'] as $item) {
            $arrData[] = ['id' => $item['id'], 'name' => $item['name'], 'creattime' => $item['creattime'], 'step' => $item['step'],];
        }
        return $arrData;
    }


    public function pipei()
    {
        $arr = [];
        $task = $this->task();
        foreach ($task as $item) {
            $data = $this->getData($item['id']);
            $arr = array_merge($arr, $data['data']);
        }


        $clue = new \app\model\Clue();
        $clueData = $clue->where([['openid', '=', 'oCSg36mgTy7jNS-AFgk1HAyJSBLY'], ['flag', '=', 2]])
            ->whereTime('createtime', date('Y-m-d'))
            ->field('clue_id,phone_number,createtime')
            ->select();
        $arrat = [];

        foreach ($clueData as $item) {
            foreach ($arr as $its) {
                if ($its['phone'] == $item['phone_number']) {
                    $url = urldecode(substr($its['record'], 62, -103));// 截取的 文件 URL
                    $file_url = self::createMake($url); // TODO 下载文件
                    $arrat[] = ['phone' => $its['phone'],'url'=>$file_url];
                    continue 2;
                }
            }
        }

        return json($arrat);
//        TODO  文件下载


//        $arr = [18816918198, 19984054752, 13419319222, 13572571327, 15233662569, 15012345866, 13649266113, 18061998299, 13850064202, 13553791661, 15285071933, 15125361067, 15012270807, 18786195101, 15287735522, 13628310930, 13452039066, 18584666468, 13888506832, 13629791912, 13658806576, 13808375303, 15340305638, 18185754922, 18996756558, 13199258222, 13885786177, 15208693346, 18208821630, 13639279399, 17716602526, 18230875666, 13638577679, 13669782172, 17749951555, 17508248534, 19336688855, 13638577333, 18728785176, 13595289529, 15186731883, 15208543298, 13212425022, 15108519283, 18996472852, 13983663462, 17783038609, 13957310400, 17385687788, 15889892655, 13765038330, 15894239857, 18696543738, 18198444204, 13466247511, 18984473663, 17828620728];
//        $data = $this->getData();
//        $data = $data['data'];
//
//        $arrat = [];
//        foreach ($arr as $item) {
//            foreach ($data as $its) {
//                if ($its['phone'] == $item) {
//                    $url = urldecode(substr($its['record'], 62, -103));
//                    $arrat[] = ['phone' => $its['phone'], 'url' => $url];
//                    continue 2;
//                }
//            }
//
//        }
//        return json($arrat);
    }

    function createMake($url)
    {

        $cont = $this->http_get_data($url);
        dump($cont);
        $filename = substr($url, strripos($url, '/'), strlen($url) - 1);
        $file = 'storage/audio/' . date('Ymd');
        if (!file_exists($file)) {
            mkdir($file, 0777, true);
        }

        file_put_contents($file . $filename, $cont);
        return $file . $filename;
    }

    // 不能用
    function http_get_data($url)    //框架放common.php
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $return_content;
    }


}