<?php

namespace app\controller;

use app\BaseController;
use think\console\command\make\Model;
use think\console\Input;
use think\facade\Db;
use think\facade\Request;
use app\controller\Payment;
use think\facade\Log;

class AdminOrder extends BaseController
{
    var $db = '';

    function __construct()
    {
        $this->db = DB::table('order_list');
    }

    // 查询订单
    function OrderDatas()
    {

        $post = Request::instance()->post();
        $pageSize = @$post['pageSize'] ?? 10;
        $pageNumber = @$post['pageNumber'] ?? 1;
        $pageCount = ($pageNumber - 1) * $pageSize;
        $sql = "SELECT 
                    a.id,a.clue_id,cart_type,out_trade_no,buy_num,price,refund_reason,creat_time,payment_time,a.flat,description,transaction_id,
                    nickname,phone_number,headimgurl
                    FROM  order_list  a
                    LEFT JOIN `user` b ON a.openid = b.openid  ORDER BY creat_time DESC LIMIT $pageCount , {$pageSize}";
        $res = DB::query($sql);
        if ($res) {
            return success(200, '获取成功', $res);
        } else {
            return error(304, '获取失败', $res);
        }
    }


    // 获取订单的数量
    function OrderCount()
    {
        $order = new \app\model\Order();
        $count = $order->count();
        return success(200, '获取成功', ['count' => $count]);
    }


    // 修改订单状态
    function EditOrderFlat()
    {
        $post = Request::instance()->post();
        if ($post['flat'] != 6 && $post['flat'] != 7) {
            return error(304, '参数错误', null);
        }

        $data = self::verify($post['id']);
        Log::info($data);
        Log::info("这是数据");
        if (!$data) {
            return error(304, '此用户不具备退款条件', null);
        }


        // === 创建退款时间所需的数据 === start
        $updata = ['flat' => $post['flat']];
        if ($post['flat'] == 7) {
            $updata['out_refund_no'] = $data['out_refund_no'];
        }
        // === 创建退款时间所需的数据 === end


        // 此处的flat 6 申述失败,7 申诉成功 做修改操作
        $row = DB::table('order_list')->where('id', $post['id'])->save($updata);
        if (!$row) {
            return error(304, '修改失败', null);
        }
        if ($post['flat'] == 6) {
            return error(200, '修改成功', null);
        }

        if ($post['flat'] == 7) {
            try {
                $pay = new Payment();
                $pay->Orderrefund($data); // 退款

                $dataB = $data['cart_type'] == 1 ? 'clue' : 'clue_old';
//                DB::table($dataB)->where([['clue_id', '=', $data['clue_id']], ['Tosell', '>', 0]])->dec('Tosell')->update();
                DB::table($dataB)->where([['clue_id', '=', $data['clue_id']], ['Tosell', '>', 0]])->save(['Tosell' => Db::raw('Tosell- ' . $data['buy_num'])]);

            } catch (\Exception $e) {
                if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                    $r = $e->getResponse();
                    $errorMessage = json_decode($r->getBody(), true);
                    if ($r->getStatusCode() == 400) {
                        DB::table('order_list')->where('id', $post['id'])->save(['flat' => 8]);
                    }

                    return error(304, $errorMessage['message'], null);
                }
            }
            return success(200, '修改成功', null);
        }
    }

    // 验证用户是否具备退款条件
    private function verify($id)
    {
        $res = $this->db->where([
            ['id', '=', $id],
            ['flat', '=', 5]
        ])->find();
        if (!$res) {
            return false;
        }

        if (empty($res['transaction_id'])) {
            return false;
        }
        $out_trade_no = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8); //退款订单号
        $res['out_refund_no'] = $out_trade_no;
        return $res;
    }

    // 验证用户退款是否到账
    function verify_refund()
    {
        $post = Request::instance()->post();
        $AesUtil = new AesUtil();
        $row = $AesUtil->decryptToString($post['resource']['associated_data'], $post['resource']['nonce'], $post['resource']['ciphertext']);
        $row = json_decode($row, true);
        DB::table('order_list')->where('out_refund_no', $row['out_refund_no'])->update(['flat' => '9']);
    }

    // 查询通话记录
    function Selectnotifyurl()
    {
        $post = Request::instance()->post();
        $res = DB::table('notifyurl')->where('out_trade_no', $post['out_trade_no'])->select();
        return success(200, '', $res);


    }


}