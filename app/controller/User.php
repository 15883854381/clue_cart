<?php

namespace app\controller;

use app\BaseController;
use app\model\User as UserModel;
use think\facade\Db;
use think\facade\Log;
use think\facade\Request;
use lunzi\TpSms;
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
//        return json($post);

        // 如果用户携带 token 就返回 数据
        // 判断token == 开始
        $token = $request->header('token');
        $res = self::verifyToken($token); // 验证token是否有效 有效则返回 数据
        if ($res) return $res;

        $code = $post['weixinCode'];
        // 判断数据是否 完善 == 开始
        if (!(isset($post['phone_number']) && isset($code))) {
            return error(304, '缺少必要参数', $post['phone_number']);
        }
        // 判断数据是否 完善 == 结束


        // 获取 AccessToken == 开始
        $accessTokenVerify = self::getAccessToken($code);
        if (!$accessTokenVerify) {
            return error(304, 'code授权失败', null);
        }
        // 获取 AccessToken == 结束

        $openid = $accessTokenVerify->openid;
        $access_token = $accessTokenVerify->access_token;
        $time = time() + 7000;
        // 从微信中获取用户的基本信息
        $res = file_get_contents('https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN');
        $res = json_decode($res);

        $cityData = self::PhonenumberCity($post['phone_number']); // 号码归属地

        // 此处做验证 验证是否上传了 userid  如果存在userid 就需要判断 这个 userid 是否存在数据库


        // 上传数据库开始
        $insertData = [
            'nickname' => $res->nickname,
            'headimgurl' => $res->headimgurl,
            'openid' => $openid,
            "phone_number" => $post['phone_number'],
            'access_token' => $access_token,
            'period_access_token' => date('Y-m-d H:i:s', $time),
            'area' => $cityData['province']
        ];

        $token = encodeToken($openid, 60, $request->host); // 授权成功 用户生成token
        $user->save($insertData);
        self::userid($openid); // 分享机制 判断 用户是否为新注册
        $insertData['token'] = $token;
        // 上传数据库结束
        return success(200, '登录成功', $insertData);
    }

    // 验证是否存在userid 分享 机制
    function userid($openid)
    {
        $post = Request::post();
        Log::info($post);
        $user = new \app\model\User();
        if (isset($post['userid'])) {
            $res = $user->where('openid', $post['userid'])->field();
            if ($res) {
                $userMap = Db::table('user_map');
                $userMap->save(['z_openid' => $post['userid'], 'x_openid' => $openid]);
            } else {
                return false;
            }
        }
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
            $usrempty = $user->where(['phone_number' => $post['phone_number']])->field('headimgurl,balance,nickname,openid,phone_number')->findOrEmpty();
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
     * 验证   token
     * @return
     */
    private function verifyToken($token)
    {
        $user = new UserModel();
        if ($token) {
            $data = decodeToken($token); // 解码token
            if ($data) {
                $usrempty = $user->where('openid', $data->id)->field('headimgurl,balance,nickname,phone_number')->findOrEmpty();
                $res = json_decode($usrempty);
                if (!empty($res)) {
                    return success(200, '登录成功', $usrempty);
                }
            }
        }
        return false;
    }


    /**
     * 获取 access_token
     * @return
     */
    private function getAccessToken($code)
    {
        $weixin = \think\facade\Config::get('WeixinConfig.Weixin');
        $res = file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $weixin['appid'] . '&secret=' . $weixin['appsecret'] . '&code=' . $code . '&grant_type=authorization_code');
        $data = json_decode($res);
        // 验证授权是否成功
        if (isset($data->errcode)) {
            return false;
        }
        return $data;
    }

    // 号码归属地
    private function PhonenumberCity($mobile)
    {
        $url = "https://api.253.com/open/unn/teladress";
        $code = \think\facade\Config::get('WeixinConfig.Code');
        $postArr = [
            'appId' => $code['appId'],
            'appKey' => $code['appKey'],
            'mobile' => $mobile,
            'orderNo' => 123132
        ];

        $response = http::post($url, [], $postArr);
        $data = json_decode($response->body, true);
        if ($data['code'] == "200000") {
            return $data['data'];
        } else {
            return null;
        }
    }


    // 获取用户基本信息 用户发布的的线索 分享的用户 收益金额
    public function UserInfo()
    {
        $order = new \app\model\Order(); // 订单
        $clue = new  \app\model\Clue(); // 线索
        $oldClue = new \app\model\OldCart(); // 旧车
        $share = new \app\model\Share(); // 分享

        $token = decodeToken();
        // 收益金额
        $orderCount = $order->where([['up_openid', '=', $token->id], ['flat', 'in', ['1','6']]])->sum('price');
        // 发布的线索数量
        $clueCount = $clue->where([['openid', '=', $token->id], ['flag', '=', 1]])->count();
        // 旧车
        $OldClueCount = $oldClue->where([['openid', '=', $token->id], ['flag', '=', 1]])->count();
        // 分享的数量
        $shareCount = $share->where('z_openid', '=', $token->id)->count();

        return success(200, '获取成功', ['orderCount' => $orderCount, 'clueCount' => $clueCount + $OldClueCount, 'shareCount' => $shareCount]);

    }


    function hello($name = 'ThinkPHP6')
    {
        return UserModel::select();
    }
}
