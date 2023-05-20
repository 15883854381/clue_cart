<?php


use app\BaseController;
use app\model\User as UserModel;
use Exception;
use think\facade\Request;
use lunzi\TpSms;
use Firebase\JWT\JWT;
use  Firebase\JWT\key;

// use think\exception\validate;
use think\exception\ValidateException;
use WpOrg\Requests\Requests as http;

class User extends BaseController
{
    protected $middleware = [\app\middleware\CheckToken::class]; // 验证登录

    // 用户授权  1.获取到前台发送的 code  2.将code 通过 API 发送给微信 获取 access_token、openid
    public function index()
    {
        $user = new UserModel();
        $request = Request::instance();
        $post = $request->post();

        // 如果用户携带 token 就返回 数据
        // 判断token == 开始
        $token = $request->header('token');
        var_dump($token);


//        return json($token);
        if ($token) {
            $data = decodeToken($token); // 解码token
            if ($data) {
                $usrempty = $user->where('openid', $data->id)->field('avatar,balance,nickname,phone_number')->findOrEmpty();
                $usrempty = json_decode($usrempty);

                if (empty($usrempty)) {
                    return error(304, '没有你的用户信息', $usrempty);
                }
                return success(200, '登录成功', $usrempty);
            } else {
                return error(304, '验证失败', null);
            }
        }
        // 判断token == 结束


        // 判断数据是否 完善 == 开始
        if (!(isset($post['phone_number']) && isset($post['weixinCode']))) {
            return error(304, '数据不完整，请检查', null);
        }
        // 判断数据是否 完善 == 结束


        $code = $post['weixinCode'];
        $res = file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxf02c02843479d12a&secret=7447f108955f746a8e4303e2dfc86b1a&code=' . $code . '&grant_type=authorization_code');
        $data = json_decode($res);
        // 验证授权是否成功
        if (isset($data->errcode)) {
            return error(304, '授权错误', null);
        }
        $token = encodeToken($data->openid, 60, $request->host); // 授权成功 生成token


        $openid = $data->openid;
        $access_token = $data->access_token;
        // 判断用户是否存在数据库中 openid 
        $usrempty = $user->where('openid', $openid)->field('avatar,balance,nickname,phone_number')->findOrEmpty();
        $usrempty = json_decode($usrempty);
        if (!empty($usrempty)) {
            $usrempty->token = $token;
            return success(200, '登录成功', $usrempty);
        }


        // 从微信中获取用户的基本信息
        $res = file_get_contents('https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN');
        $res = json_decode($res);
        // 上传数据库开始
        $insertData = [
            'nickname' => $res->nickname,
            'avatar' => $res->headimgurl,
            'openid' => $openid,
            'refresh_token' => $data->refresh_token,
            "phone_number" => $post['phone_number'],
        ];
        $user->save($insertData);
        $insertData['token'] = $token;
        // 上传数据库结束
        return success(200, '登录成功', $insertData);
    }

    /**
     * 验证验证码
     */
    public function getcode()
    {
        $request = Request::instance();
        $user = new UserModel();
        $tpSms = new TpSms(); // 生成验证码的

        $post = $request->post();
        $tpSms->mobile($post['phone_number']);
        $tpSms->code($post['code']);
        if (!$tpSms->check()) {
            return error(304, '验证码错误', $tpSms->getErrorMsg());
        } else {
            // 登录时间 验证用户 是否存在 不存在 返回 201 前台 跳转授权  200 用在本地存在 直接返回数据
            $usrempty = $user->where(['phone_number' => $post['phone_number']])->field('avatar,balance,nickname,openid,phone_number')->findOrEmpty();
            $usrempty = json_decode($usrempty);
            if (!empty($usrempty)) {
                $usrempty->token = encodeToken($usrempty->openid); // 生成 token
                unset($usrempty->openid);
                return success(200, '登录成功', $usrempty);
            } else {


                // 判断数据是否 完善 == 开始
                if (!(isset($post['phone_number']))) {
                    return error(304, '数据不完整，请检查', null);
                }
                // 判断数据是否 完善 == 结束


                // 验证数据是否为空 == 开始
                try {
                    $dataArr = [
//                        'username' => $post['username'],
                        'phone_number' => $post['phone_number'],

                    ];

                    validate(
                        ['phone_number' => 'require|number|length:11'],
                        ['phone_number.require' => '请填写手机号码', 'phone_number.number' => '请正确填写手机号码', 'phone_number.length' => '请正确填写手机号码']
                    )->check($dataArr);
                } catch (ValidateException $e) {
                    return error(304, $e->getError(), null);
                }
                // 验证数据是否为空 == 结束
                return success(201, '验证成功', null);
            }
        }
    }


    private function Datavalidate($dataArr)
    {
        // 验证数据是否为空 == 开始
        try {
            // $dataArr = [
            //     'username' => $post['username'],
            //     'phone_number' => $post['phone_number'],
            //     'weixinCode' => $post['weixinCode'],
            // ];

//            validate(
//                ['username' => 'require|chs|length:2,8'],
//                ['username.require' => '请填写用户名', 'username.chs' => '用户名必须是汉字', 'username.length' => '请正确填写用户名']
//            )->check($dataArr);
            validate(
                ['phone_number' => 'require|number|length:11'],
                ['phone_number.require' => '请填写手机号码', 'phone_number.number' => '请正确填写手机号码', 'phone_number.length' => '请正确填写手机号码']
            )->check($dataArr);
            // validate(
            //     ['weixinCode' => 'require'],
            //     ['weixinCode.require' => '你还没有授权']
            // )->check($dataArr);
            return true;
        } catch (ValidateException $e) {
            return $e->getMessage();
            // return error(304, $e->getError(), null);
        }
        // 验证数据是否为空 == 结束
    }


    // 测试短信平台验证码发送
    public function testUserCode()
    {
        $testConfig = \think\facade\Config::get('WeixinConfig');


        $postArr = array(
            'account' => $testConfig['Code']['account'],
            'password' => $testConfig['Code']['password'],
            'msg' => "【汽车线索互助联盟】您此次验证码为123456，5分钟内有效，请您尽快验证！",
            'phone' => '15883854381',
            'report' => true
        );
        $header = [
            'Content-Type' => 'application/json;charset=utf-8'
        ];
        $response = http::post('https://smssh1.253.com/msg/v1/send/json', $header, json_encode($postArr));
        $data = json_decode($response->body, true);
        if ($data['code'] != '0') {
            return error($data['code'], $data['errorMsg'], null);
        }
        return 'success';
    }


    /**
     * 验证token
     * @return void
     */
    private function verifyToken()
    {
        $request = Request::instance();
        $user = new UserModel();
        $token = $request->header('token');
        if ($token) {
            $data = decodeToken($token); // 解码token
            if ($data) {
                $usrempty = $user->where('openid', $data->id)->field('avatar,balance,nickname,phone_number')->findOrEmpty();
                $res = json_decode($usrempty);
                if (!empty($res)) {
                    return success(200, '登录成功', $usrempty);
                }
//                return success(200, '登录成功', $usrempty);
            }
//            else {
//                return error(304, '验证失败', null);
//            }
        }
//        return false;
    }


    public function hello($name = 'ThinkPHP6')
    {
        return UserModel::select();
    }
}
