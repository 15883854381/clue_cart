<?php

namespace app\job;

use app\controller\Payment;
use think\queue\Job;
use \think\facade\Db;

class OrderPayment
{

    public function fire(Job $job, $data)
    {
        $type = $data['type'] == 1 ? 'clue' : "clue_old";

        $Order = Db::table('order_list');
        $res = $Order->where('out_trade_no', $data['out_trade_no'])->find();
        if ($res['flat'] == 2) {
            (new Payment())->closeOutTradeNo($data['out_trade_no']);
            $Order->where('out_trade_no', $data['out_trade_no'])->update(['flat' => 8]);
            Db::table($type)->where('clue_id', $data['clue_id'])->dec('Tosell', $res['buy_num'])->update();
            echo '我执行了取消订单';
        } else {
            echo '我没有执行取消订单';
        }
        $job->delete();

    }


}