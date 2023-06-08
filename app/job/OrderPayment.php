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

        //....这里执行具体的任务
//        print_r($data);
//        if ($job->attempts() > 3) {
//            //通过这个方法可以检查这个任务已经重试了几次了
//        }
//
//
//        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法

//
//        // 也可以重新发布这个任务
//        $job->release($delay); //$delay为延迟时间

    }

//    public function failed($data)
//    {
//
//        // ...任务达到最大重试次数后，失败了
//    }

}