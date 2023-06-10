<?php

namespace app\controller;

use app\BaseController;

use app\model\User as UserModel;

use app\Ulits\JSSDK;
use think\facade\Db;
use think\facade\Log;
use think\facade\Session;
use think\facade\Request;
use lunzi\TpSms;
use think\exception\ValidateException;
use WeChatPay\Formatter;
use WpOrg\Requests\Requests as http;
use think\cache\driver\Redis;
use think\facade\Cache;

//需要使用到的类
use think\facade\Config;


class Ulits extends BaseController
{
    protected $middleware = [\app\middleware\CheckToken::class];

    /**
     * 生成验证码
     */
    public function sendcode()
    {

        $request = Request::instance();
        $post = $request->post();
        // 发送验证码之前对 用户名和手机号码进行验证  == 开始
        $dataArr = [
            'phone_number' => $post['phone_number'],
        ];

        // 验证是否传入的手机号和用户名是否符合规则
        $vili = self::Datavalidate($dataArr);
        if ($vili !== true) {
            return error(304, $vili, null);
        }
        // 发送验证码之前对 用户名和手机号码进行验证  == 结束
        if (empty($post['phone_number'])) {
            return success(300, '未填写手机号码', null);
        }
        $code = (new TpSms());
        $code->mobile($post['phone_number']);
        $stateCoded = $code->create();
//        $res = self::platformCodeSend($stateCoded, $post['phone_number']);
//        if ($res != 'success') {
//            return $res;
//        }
        return success(200, '发送成功', $stateCoded);
    }

    /**
     * 使用平台接口 发送验证码
     * @param $code String 验证码
     * @param $phone String 手机号码
     * @return string|\think\response\Json
     */
    public function platformCodeSend($code, $phone)
    {
        $testConfig = \think\facade\Config::get('WeixinConfig');
        $postArr = array(
            'account' => $testConfig['Code']['account'],
            'password' => $testConfig['Code']['password'],
            'msg' => "【汽车线索互助联盟】您此次验证码为" . $code . "，5分钟内有效，请您尽快验证！",
            'phone' => $phone,
            'report' => true
        );
        $header = ['Content-Type' => 'application/json;charset=utf-8'];
        $response = http::post('https://smssh1.253.com/msg/v1/send/json', $header, json_encode($postArr));
        $data = json_decode($response->body, true);
        if ($data['code'] != '0') {
            return error($data['code'], $data['errorMsg'], null);
        }
        return 'success';
    }


    /**
     * 验证验证码
     */
    public function getcode()
    {
        $request = Request::instance();
        $post = $request->post();
        $tpSms = new TpSms();
        $tpSms->mobile($post['phone_number']);
        $tpSms->code($post['code']);
        if (!$tpSms->check()) {
            return false;
        } else {
            return true;
        }
    }


//    验证手机号码
    private function Datavalidate($dataArr)
    {
        // 验证数据是否为空 == 开始
        try {
            validate(
                ['phone_number' => 'require|number|length:11'],
                ['phone_number.require' => '请填写手机号码', 'phone_number.number' => '请正确填写手机号码', 'phone_number.length' => '请正确填写手机号码']
            )->check($dataArr);

            return true;
        } catch (ValidateException $e) {
            return $e->getMessage();
        }
        // 验证数据是否为空 == 结束
    }

    /**
     * 获取城市数据
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function city()
    {
        $redis = new Redis(Config::get('cache.stores.redis'));
        $t_province = $redis->get('t_province');
        if (!$t_province) {
            $t_province = Db::table('t_province')->field('id,name as text')->select()->toArray();
            $t_city = Db::table('t_city')->field('id,name as text,province_id')->select()->toArray();
            $i = 0;
            foreach ($t_province as $item) {
                $city = [];
                foreach ($t_city as $it) {
                    if ($item['id'] == $it['province_id']) {
                        $city[] = $it;
                    }
                }
                $t_province[$i]['children'] = $city;
                $i++;
            }
            $redis->set('t_province', $t_province, 7200);
        }

        return success('200', 're', $t_province);
    }


//    获取汽车品牌
    public function CarBrand()
    {
        $redis = new Redis(Config::get('cache.stores.redis'));
        $Car_Brand = $redis->get('Car_Brand');
        if (!$Car_Brand) {
            $Car_Brand = Db::table('t_car_brand')->field('id,name')->select();
            $redis->set('Car_Brand', $Car_Brand, 7200);
        }
        return success('200', '', $Car_Brand);
    }

//    获取用户标签
    public function userTags()
    {

        $post = Request::post();
        $WHERE_SUB = [['sortid', '=', 0]];
        $WHERE_TAGS = "";
        if (isset($post['cart_type'])) {
            $WHERE_SUB[] = ['cart_type', '=', $post['cart_type']];
            $WHERE_TAGS .= ' and a.cart_type = ' . $post['cart_type'];
        }
        $sql = "SELECT a.tagName as text,a.sortid,a.id FROM tags a LEFT JOIN (SELECT * FROM tags) b ON a.id = b.sortid WHERE a.sortid != 0 $WHERE_TAGS  ORDER BY a.sortid DESC";
        $Car_tags = Db::table('tags')->field("id,tagName as text")->where($WHERE_SUB)->select()->toArray();
        $Car_tags_sub = Db::query($sql);
        $i = 0;
        foreach ($Car_tags as $item) {
            foreach ($Car_tags_sub as $it) {
                if ($it['sortid'] == $item['id']) {
                    $Car_tags[$i]['children'][] = $it;
                }
            }
            $i++;
        }
        return success('200', '', $Car_tags);
    }


    /**
     * 授权验证
     * @return
     */
    static function authority_verify()
    {
        $token = decodeToken();  // 解码token
        $user = new \app\model\User();
        $res = $user->where('openid', $token->id)->find();
        if (empty($res)) {
//            return error(401, '没有你的数据', $res);
            return ['code' => 401, 'mes' => '没有你的数据', 'data' => null];
        }
        switch ($res['flas']) {
            case '0':
//                return error(306, '请上传审核资料', null);
                return ['code' => 306, 'mes' => '请上传资料审核 【也可联系客服进行审核】', 'data' => null];
            case '2':
//                return error(307, '你的资料审核不通过，请重新上传资料', null);
                return ['code' => 307, 'mes' => '你的资料审核不通过，请重新审核资料上传', 'data' => null];
            case '3':
//                return error(308, '资料审核中，请通过审核通过后再上传', null);
                return ['code' => 308, 'mes' => '资料审核中...', 'data' => null];
            case '4':
//                return error(309, '你还不具备购买条件，若需购买请联系客服', null);
                return ['code' => 309, 'mes' => '你还没有购买权限，若需购买请联系客服', 'data' => null];
            case '5':
//                return error(400, '你还不具备上传条件，若需上传请联系客服', null);
                return ['code' => 400, 'mes' => '你还不具备上传条件，若需上传请联系客服', 'data' => null];
        }
//        4 只能上传 5 只能购买
        return ['code' => 200, 'mes' => '', 'data' => null];
    }

    /**
     * 验证用户是否登录
     * @return \think\response\Json
     */
    public function loginVerify()
    {
        $request = Request::instance();
        $token = $request->header('token');
        $data = decodeToken($token); // 解码token
        if (!$data) {
            return error(304, '没有登录', null);
        }
        $es = self::authority_verify();
        if ($es['code'] != 200) return json($es);
        return success(200, '已登录', null);
    }


    // 验证用户是否购买了此线索
    public function VerifyOrder()
    {
        $request = Request::instance()->post();
        $token = decodeToken();
        $clue_id = $request['id'];
        $res = Db::table('order_list')->where(['openid' => $token->id, 'clue_id' => $clue_id])->find();
        if (!$res) {
            return false;
        }
        return $res;


    }


    // 获取签名数据集   wx.config({})
    public function signJsapi()
    {
        $post = Request::post();
        $access_token = self::GetAccess_token();
        if (!$access_token) {
            Log::error('获取token出错');
            return false;
        }
        $jsapiTicket = self::GetjsapiTicket($access_token);
        if (!$jsapiTicket) {
            Log::error('获取jsapi出错');
            return false;
        }
        $data = self::sign($jsapiTicket, $post['url']);
        return $data;

    }

    // 获取access_token 搭配 signJsapi 获取签名
    public function GetAccess_token()
    {
        $redis = new Redis(Config::get('cache.stores.redis'));
        $access_token = $redis->get('access_token');
        if ($access_token) {
            return $access_token;
        }

        $Weixin = \think\facade\Config::get('WeixinConfig.Weixin');
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $Weixin['appid'] . '&secret=' . $Weixin['appsecret'];
        $res = http::get($url);
        Log::info($res);
        $data = json_decode($res->body, true);
        if (isset($data['errcode'])) {
            Log::info('获取Access_token出错==={errcode}', ['errcode' => json_encode($data)]);
            return false;
        }
        Log::info('这个是access_token====' . $data['access_token']);
        $redis->set('access_token', $data['access_token'], 7000);
        return $data['access_token'];
    }

    // 获取 jsapi_ticket 搭配 GetAccess_token
    protected function GetjsapiTicket($access_token)
    {
        $redis = new Redis(Config::get('cache.stores.redis'));
        $ticket = $redis->get('ticket');
        if ($ticket) {
            return $ticket;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $access_token . "&type=jsapi";
        $res = http::get($url);
        $data = json_decode($res->body, true);
        if ($data['errcode'] != 0) {
            Log::info('获取jsapiTicket出错 ====  {errcode}', ['errcode' => json_encode($data)]);
            return false;
        }
        Log::info('这个是ticket====' . $data['ticket']);
        $redis->set('ticket', $data['ticket'], 7000);
        return $data['ticket'];
    }

    // 生成 签名 数据集 搭配 GetAccess_token
    protected function sign($jsapi_ticket, $url = '')
    {
        $Weixin = \think\facade\Config::get('WeixinConfig.Weixin');
        $data = [
            'noncestr' => Formatter::nonce(),
            'jsapi_ticket' => $jsapi_ticket,
            'timestamp' => (string)Formatter::timestamp(),
            'url' => $url,
        ];

        ksort($data);
        $res = urldecode(http_build_query($data, '', '&'));
        Log::info($res);
        $data['signature'] = sha1($res);
        $data['appId'] = $Weixin['appid'];
        return $data;
    }


    // 获取用户的ID 用于分享
    public function UserId()
    {
        $token = decodeToken();
        if ($token) {
            return success(200, '获取成功', ['id' => $token->id]);
        } else {
            return error(304, '获取成功', ['id' => null]);
        }
    }

    /**
     *短信平台 检测手机号码 有效
     * @param $mobiles
     * @return false|mixed
     */
    public function batchUcheck($mobiles)
    {
        $Code = Config::get('WeixinConfig.Code');
        $params = [
            'appId' => $Code['appId'], // appId,登录万数平台查看
            'appKey' => $Code['appKey'], // appKey,登录万数平台查看
            'mobiles' => $mobiles, // 要检测的手机号，多个手机号码用英文半角逗号隔开
        ];
        $response = http::post('https://api.253.com/open/unn/batch-ucheck', [], $params);

        $data = json_decode($response->body, true);
        if (!isset($data['code']) or $data['code'] != '200000') {
            Log::error($data);
            return false;
        }
        // 手机状态
        if ($data['data'][0]['status'] != 1 && $data['data'][0]['status'] != 4) {
            return false;
        }
        return $data['data'][0];
    }

    // 模糊查询汽车品牌
    public function FuzzyQueriesCarBrand($brand)
    {
        $sql = "select b_id,b_name from carhouse_car_info where   c_series_name like '%$brand%'  LIMIT 1";
        return Db::query($sql);

    }


    // 模糊查询城市省市
    public function FuzzyQueriesCity($City)
    {
        $sql = "SELECT CONCAT(a.name,b.name) as city,b.province_id,b.id  FROM t_province a  LEFT JOIN t_city b ON a.id = b.province_id HAVING city LIKE '%$City%'";
        return Db::query($sql);
    }


}
