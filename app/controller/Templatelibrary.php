<?php

namespace app\controller;

use app\BaseController;
use think\App;
use think\cache\driver\Redis;
use think\facade\Config;
use think\facade\Log;
use WpOrg\Requests\Requests as http;
use app\controller\Ulits;

class Templatelibrary extends BaseController
{

    public $weixin;
    public $url;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $ulits = new Ulits($app);
        $access_token = $ulits->GetAccess_token();
        $this->url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
        $this->weixin = Config::get('WeixinConfig.Weixin');
    }


    /**
     * 订车通知模板
     * @param $user array  openid   user_name
     * @return string
     */

    function template_one(array $user)
    {
        $date = date("Y-m-d");
        $sex = $user['sex'] == 1 ? '先生' : '女士';
        $updata = [
            'touser' => $user['openid'],
            'template_id' => '-Jv0Adc4A5cyiCNq0fPmJb8d_qeY_sYUlc4SM_T_-xU',
            'appid' => $this->weixin['appid'],
            "url" => $this->weixin['Cline_url'],
            "data" => [
                "thing1" => ["value" => $user['user_name'] . $sex . "【${user['province']}.${user['city']}】"],
                "thing2" => ["value" => "【" . $user['car'] . "】"],
                "thing3" => ["value" => $user['phone_number']],
                "time4" => ["value" => $date],
            ]
        ];
        $code_res = http::post($this->url, [], json_encode($updata));
        return $code_res->body;
    }

    /**
     * 配对成功通知
     * @param $user array  openid 接收者的openid
     * @param $date string 日期
     * @return string
     */
    function template_two(array $user, string $date = ''): string
    {
        $date = date("Y-m-d");
        $sex = $user['sex'] == 1 ? '先生' : '女士';
        $updata = [
            'touser' => $user['openid'],
            'template_id' => 'pbh7JKyKeIyYqKOJpcYJ-zZ0Qgda6WBgKEJNbLJ08Xs',
            'appid' => $this->weixin['appid'],
            "url" => $this->weixin['Cline_url'],
            "data" => [
                "first" => '最新线索通知',
                "name" => ["value" => $user['user_name'] . $sex . "【${user['province']}.${user['city']}】"],
                "sex" => ["value" => $sex],
                "tel" => ["value" => $user['phone_number']],
                "remark" => ["value" => $user['user_name'] . $sex . "最近有购车意向，请留意此线索。发布于：" . $date],
            ]
        ];
        $code_res = http::post($this->url, [], json_encode($updata));
        return $code_res->body;
    }

}
