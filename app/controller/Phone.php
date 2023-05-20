<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Log;
use think\facade\Request;
use think\response\Json;
use WpOrg\Requests\Requests as http;

class Phone extends BaseController
{
    // 拨打电话
    public function CallingPhone()
    {
        $data = self::selectPhone();// 拨打者的电话号码
        if (!$data) {
            return error(304, '呼叫失败，请联系客服', null);
        }

        $phone = \think\facade\Config::get('WeixinConfig.phone');
//        AccessKey 与 TelA 与 TelX 与 TelB 与 AppSecret 五个参数值按此顺序MD5加密后转大写
        $data = [
            "AccessKey" => $phone['accesskey'],
            "TelA" => $data['telA'],//主动发起人
            "TelX" => $phone['TelX'],// 中间人
            "TelB" => $data['telB'],// 被动发起人
            "Expiration" => 15,
            "NotifyUrl" => "http://h.199909.xyz/NotifyUrl/" . $data['out_trade_no'],
            "Signature" => strtoupper(md5($phone['accesskey'] . $data['telA'] . $phone['TelX'] . $data['telB'] . $phone['appSecret']))
        ];


//        return json($data);
        $response = http::post($phone['url'] . '/api/call/bind', [], $data);
        $res = json_decode($response->body, true);
        if ($res['code'] != 0) {
            return error(304, $res['msg'], null);
        }
        return success(200, '回拨线路接通中请注意接听', null);
    }

    // 话单推送
    function NotifyUrl($outTradeNo)
    {
        try {
            $request = Request::instance()->post();
            Db::table('order_list')->where([['out_trade_no', '=', $outTradeNo], ['flat', '=', '3']])->update(['flat' => '4']);
            $request['out_trade_no'] = $outTradeNo;
            if ($request['bind_id']) {
                Db::table('notifyurl')->save($request);
                return json(["code" => "0", "message" => "success"]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private function selectPhone()
    {
        $user = new \app\model\User();
        $clue = new \app\model\Clue();
        // 查询主动呼叫的号码
        $token = decodeToken();  // 解码token
        $res = $user->where('openid', $token->id)->field('phone_number AS telA')->find();
        if (!$res) {
            return false;
        }

        // 查询被呼叫的电话号码
        $request = Request::instance()->post();
        if (!isset($request['clue_id'])) {
            return false;
        }
        $data = $clue->where('clue_id', $request['clue_id'])->field('phone_number  AS telB')->find();
        if (!$data) {
            return false;
        }
        $res['telB'] = $data['telB'];
        $res['out_trade_no'] = $request['out_trade_no'];
        return $res;

    }


}